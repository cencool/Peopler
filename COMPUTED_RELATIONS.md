# Computed Relations — Analysis & Upgrade Proposal

> Focused companion to `ARCHITECTURE.md`. Covers the current
> `Person::computedRelations()` implementation, why it only finds shallow
> relations, and how to extend it to deeper implicit relations.

## 1. What "computed relations" means

Users enter only **explicit** relations (`person_relation` rows), e.g.
"Bob is Alice's son", "Carol is Bob's daughter". From these we can **infer
implicit** relations that were never entered: "Carol is Alice's grandchild".

This is a **graph problem**: people are nodes; each explicit relation is a
labeled directed edge (and its complement, the reverse edge). Inferring an
implicit relation = **finding a path** from source person to a target and
**composing the edge labels** along that path into a single relation label,
using a *kinship algebra*.

## 2. The building blocks already in place

- Every relation collapses to a gender-neutral **token** (`relation_name.token`):
  `parent, child, sibling, partner, grandparent, grandchild, uncle, nephew,
  cousin, *-in-law, ...`.
- A token + a target gender maps back to a display label
  (`token=grandparent, gender=m` → `grandfather`).
- `computedRelations()` composes tokens with a hardcoded 2-step table
  `$tokenChains[tokenA][tokenB] = resultToken`.

The token idea is exactly right. The *traversal* around it is the weak part.

## 3. Current implementation — how it works

`models/basic/Person.php:119` (`computedRelations()`):

```
tokenChains = {                      // 2-hop composition, base tokens only
  child:   {child:grandchild, sibling:nephew,  partner:child,          parent:sibling},
  sibling: {child:child,      sibling:sibling,  partner:sibling-in-law, parent:uncle},
  partner: {child:child-in-law,sibling:sibling-in-law,partner:partner,  parent:parent},
  parent:  {child:partner,    sibling:parent,   partner:parent-in-law,  parent:grandparent},
}

currentRelations = givenRelations(self)
do:
  newRelations = []
  for relationA in currentRelations:          # self --tokenA--> B
     tokenA = token(relationA)
     B = Person::findOne(relationA.to_whom_id)
     for relationB in givenRelations(B):       # B --tokenB--> C
        if relationB.to_whom == self: skip
        tokenB = token(relationB)
        result = tokenChains[tokenA][tokenB]    # index is ALWAYS chain[0].chain[1]
        if result:
           rel = label(result, self.gender)
           if not already given/computed: computedRelations += (self --rel--> C)
           newRelations += (self --rel--> C)
  currentRelations = newRelations
while newRelations not empty
```

## 4. Why it only finds shallow relations (root causes)

1. **The composition table only keys the four base tokens**
   (`child/sibling/partner/parent`). The moment a path produces a *compound*
   token (`grandchild`, `uncle`, `nephew`, `grandparent`, `*-in-law`, `cousin`),
   there is **no `tokenChains[compound][...]` entry**, so it can never be
   extended further.

2. **The path token is not carried forward.** The index is hardcoded
   `currentTokenChain[0] . '.' . currentTokenChain[1]` and the chain is reset
   (`$currentTokenChain = []`) every outer iteration. So even though there's a
   `do/while` that *looks* iterative, each pass only ever composes **exactly two
   tokens**. Combined with (1), the effective reach is **depth 2, and only when
   both hops are base tokens**.

3. **Second-pass mislabeling.** On pass 2, `currentRelations` holds the
   *previously computed* relations whose `.relation` is a compound label; the
   code re-derives `tokenA` from that label. Because compound tokens aren't in
   the table, results are dropped — the loop terminates rather than deepening.

4. **Performance / correctness noise:** repeated `RelationName::find()->one()`
   for the same token (N+1 queries), `Person::findOne()` per neighbor (also
   applies the owner scope, silently cutting chains that cross owners), and
   cycle-avoidance done only by de-duplicating final `(to_whom_id, relation)`
   pairs rather than tracking visited nodes.

**Net effect:** grandchild/grandparent/uncle/nephew/in-law at 2 hops work;
great-grandchild, grand-nephew, cousin-of-cousin, "sibling's spouse's parent",
etc. never appear.

## 5. Target design — BFS over the kinship graph

Treat it as **breadth-first search from the source person**, carrying a
*cumulative token* that describes `source → currentNode`. BFS is the right choice
because the **shortest** path yields the **most direct** kinship term
(you want `mother`, not `sister's-mother`).

```
function computedRelations(source, MAX_DEPTH = 4):
    adj    = buildAdjacency(owner)          # personId -> [(neighborId, edgeToken)]
    given  = givenTargets(source)           # set of to_whom_id already explicit
    visited = { source }                    # assign each person ONE shortest label
    queue  = [ (source, tokenSelf, depth=0) ]
    out    = []

    while queue not empty:
        (u, pathToken, depth) = queue.popleft()
        if depth == MAX_DEPTH: continue
        for (v, edgeToken) in adj[u]:
            if v in visited: continue
            newToken = compose(pathToken, edgeToken)   # kinship algebra
            if newToken is null: continue              # not expressible -> prune
            visited.add(v)
            if v not in given:
                out += relationItem(source, v, label(newToken, gender(v)))
            queue.push( (v, newToken, depth+1) )
    return out
```

Key differences from today:

| Today                                  | Proposed                                  |
|----------------------------------------|-------------------------------------------|
| 2-hop, base tokens only                | arbitrary depth up to `MAX_DEPTH`         |
| path token reset each pass             | cumulative `pathToken` carried forward    |
| de-dup by (id,relation)                | `visited` set → one shortest label/person |
| `findOne` per neighbor (owner-scoped)  | adjacency built once from a few queries   |
| composition = fixed 2-key table        | `compose()` algebra (see §6)              |

`buildAdjacency()` should load the owner's `person_relation` rows once, and for
each stored edge emit **both** directed labeled edges (forward = stored token;
reverse = complement token via `relation_pair`), keyed by person id. That removes
the N+1 queries and the accidental owner-scope truncation mid-chain.

## 6. The `compose(a, b)` kinship algebra — two strategies

`compose()` is the real substance. Two ways to build it, in increasing power:

### Strategy A — Extended composition table (pragmatic, recommended first step)
Keep the token-pair table but (a) make **compound tokens valid left operands**
and (b) return `null` for undefined pairs (prune) instead of silently stopping
the whole search. Example additions:

```
grandchild + child   = great-grandchild
grandchild + sibling = grand-nephew
grandparent+ parent  = great-grandparent
grandparent+ sibling = grand-uncle
sibling    + child   = nephew        # already implied
parent + parent      = grandparent   # already there
uncle  + child       = cousin
cousin + child       = cousin-once-removed
...
```

- ✅ Simple, incremental, easy to unit-test pair-by-pair.
- ✅ Deep search works immediately for any pair you define.
- ⚠️ Combinatorial: N tokens → up to N² entries; gaps just prune (acceptable).
- ⚠️ Needs new vocabulary rows (`relation_name`) + translations for the new
    tokens, otherwise `label()` can't name them (see §7).

### Strategy B — Structured kinship coordinates (principled, unbounded depth)
Represent a **blood** relationship as a small vector instead of an opaque token:

```
(up, down, throughMarriage, isPartner?)
  parent  = (1,0)      child = (0,1)     self = (0,0)
  sibling = (1,1) via nearest common ancestor
  grandparent=(2,0)  grandchild=(0,2)  uncle=(2,1)  nephew=(1,2)  cousin=(2,2)
```

Composition becomes **arithmetic on the coordinate** (walk up to a common
ancestor, then down), and naming is derived from the coordinate:

```
up>0,down=0      -> (great-)^(up-2) grandparent / parent
up=0,down>0      -> (great-)^(down-2) grandchild / child
up==down>=2      -> (up-1)th cousin
up>down          -> (grand-)^(up-2) uncle/aunt
down>up          -> (grand-)^(down-2) nephew/niece
+ marriage step  -> "-in-law"
```

- ✅ Scales to arbitrary depth with *correct* names automatically (great-great-…,
    cousins by degree, "twice removed").
- ✅ No giant table to maintain.
- ⚠️ More upfront logic; partner/in-law/step and "unknown gender" need explicit
    handling; naming rules (cousin "removed", grand-uncle) get fiddly.

**Recommendation:** ship **Strategy A first** — it's a small, safe diff on top of
the existing token idea and immediately removes the depth-2 ceiling. Move
`compose()` and the table into a dedicated `KinshipCalculus` helper class so the
BFS never changes; later you can swap the table for Strategy B behind the same
`compose()` interface if you want unbounded, auto-named depth.

## 7. Vocabulary & translation gap (don't skip)

Deep relations are only *displayable* if two things exist for each new token:
a **`relation_name` row** (gendered label per gender) and, for Slovak, a
**translation entry**. Current vocabulary stops at
grandparent/grandchild/uncle/nephew/cousin — there is **no** great-grandparent,
grand-nephew, or cousin-degree. Plan a companion migration/seed for the tokens
your chosen `MAX_DEPTH` can produce, plus a generic `relative` fallback in
`label()` so unnamed distant relatives still render rather than being dropped.

### How i18n actually works here (important)
Labels are rendered with `Yii::t('app-m', $label)` / `Yii::t('app-f', $label)`
(`Person::relations()`, `models/basic/Person.php:233`). Yii uses the **source
string as the key**:
- **English** needs *no* file — there is no `messages/en-US/`; Yii returns the
  `relation_name` verbatim (`'grandfather'` → `'grandfather'`). (Note: there are
  **no** top-level `messages/app-m.php`/`app-f.php` files; the only translation
  files are the three under `messages/sk-SK/`.)
- **Slovak** requires an explicit entry in `messages/sk-SK/app-m.php` (male
  labels) and `messages/sk-SK/app-f.php` (female labels).

The existing relation vocabulary is **already fully translated** to Slovak; only
the *new* deep-relation tokens need entries. Slovak forms:

| New token (English base)  | sk `app-m` (male) | sk `app-f` (female) |
|---------------------------|-------------------|---------------------|
| great-grandparent         | `pradedko`        | `prababka`          |
| great-grandchild          | `pravnuk`         | `pravnučka`         |
| great-great-grandparent   | `prapradedko`     | `praprababka`       |
| great-great-grandchild    | `prapravnuk`      | `prapravnučka`      |
| grand-/great-uncle·aunt   | `prastrýko`       | `prateta`           |
| grand-/great-nephew·niece | `prasynovec`      | `praneter`          |
| cousin (already seeded)   | `bratranec`       | `sesternica`        |
| **generic fallback**      | `príbuzný`        | `príbuzná`          |

Two facts that shape the design:
1. **Slovak stacks the prefix `pra-` per extra generation** (great- → `pra`,
   great-great- → `prapra`), mirroring English `great-`. So with the
   coordinate-based algebra (Strategy B, §6) both languages' names can be
   **generated** — `str_repeat('pra', n) . base` for Slovak,
   `str_repeat('great-', n) . base` for English — instead of stored per token.
2. **Cousin-degrees and deep in-law chains have no single word** in Slovak or
   English (Slovak uses phrases like *"bratranec z druhého kolena"*). This is the
   concrete reason the `relative` → `príbuzný`/`príbuzná` fallback is needed: past
   the nameable terms, show the generic word rather than nothing.

## 8. Practical guardrails

- **`MAX_DEPTH`** (config param, e.g. 3–4). Family subgraphs are dense; without a
  cap and a `visited` set the search is exponential.
- **`visited` set** keyed by person id → each person gets exactly one (shortest)
  computed label; also kills cycles.
- **Skip explicit + self**: never emit a computed relation for a person who
  already has a given relation to the source, nor for the source itself
  (both handled above).
- **Visibility scope**: build the adjacency to mirror `Person::find()` exactly —
  **`admin` sees every person (no owner filter)**, a normal user is scoped to
  their own. This matters because explicit relations can span owners (e.g. a
  `demo` person who is a sibling of an `admin` person); scoping the whole graph
  to one owner would silently drop that edge and every relation implied through
  it. (This was a real regression: person 97's uncle 53 vanished because 53 is
  owned by `demo` — fixed by unscoping for admin, 2026-07-18.)
- **Keep the output shape** `{relation_id:-1, to_whom_id, relation_to_whom,
  relation}` — the web view (`personView.php:113`) and API
  (`RelationController::actionView`) already depend on `relation_id == -1`
  marking a non-editable computed relation.
- **Testability**: with `compose()` isolated, unit-test the algebra on token
  pairs and test the BFS on a small fixture graph independent of the DB.

## 9. Suggested incremental plan

1. Extract adjacency building + `compose()` into a `KinshipCalculus` /
   `RelationGraph` helper (no behavior change yet; move the existing table in).
2. Replace the `do/while` body in `computedRelations()` with the BFS in §5,
   carrying `pathToken`, using `visited` and `MAX_DEPTH`.
3. Extend the composition table (Strategy A) + add missing `relation_name`
   tokens and translations; add a generic-`relative` fallback in `label()`.
4. Add unit tests for `compose()` pairs and a fixture-graph BFS test.
5. (Optional, later) Swap the table for coordinate-based composition (Strategy B)
   for unbounded auto-named depth.

## 10. Implementation status (done)

Implemented on branch `dev` (2026-07-18):

- **`models/basic/KinshipCalculus.php`** — pure algebra. Blood relations use the
  coordinate model directly (Strategy B for the consanguineal part): `COORDS`
  maps tokens to `[up, down]`, `compose()` applies
  `up = a1 + max(0, a2 − d1)`, `down = d2 + max(0, d1 − a2)`, and
  `coordToToken()` names the result (auto-extends to great-/grand-, falls back to
  `relative`). Marriage/in-law one-step cases use the explicit `MARRIAGE` table
  (Strategy A); `tokenComplement()` gives reverse-edge tokens.
- **Edge direction (critical):** a stored row `(A, token_ab, B)` means
  "**A is token_ab of B**" (label gender = A's). So `buildAdjacency()` puts
  `token_ab` on edge **B → A** and the complement on **A → B**. Getting this
  backwards produced a wrong `son` instead of `uncle` for person 47→91 (validated
  against live data 2026-07-18). See `ARCHITECTURE.md` §4.
- **Output convention (critical):** the graph internally uses the form "target
  **is** `token` **of** source" (compose's result describes the *target*). But
  the app — like given relations — lists a person's relations as "**this person
  is `<relation>` of `<to_whom>`**" (label describes the *source*, gendered by
  the source). So `traverse()` emits `tokenComplement(newToken)` gendered by the
  **source**. Emitting the raw token showed "97 is uncle of 53" when the truth is
  "97 is *nephew* of 53" (fixed 2026-07-18).
- **`models/basic/KinshipResolver.php`** — builds the visibility-scoped adjacency
  in a single SQL query (both directions via `tokenComplement`), then
  `traverse()` runs the BFS with `visited` + `DEFAULT_MAX_DEPTH = 4`. `traverse()`
  takes the graph as arguments (no DB) so it is unit-testable; `compute()` wires
  in the DB. The constructor takes the source person's gender for labelling the
  output.
- **Both `Person::computedRelations()`** (web `models/basic` + `modules/v1`) now
  delegate: `return (new KinshipResolver($this->id, $this->owner, $this->gender))->compute();`.
  Output shape and the `relation_id = -1` marker are unchanged. Dead
  `tokenChains` / `checkRelationExists` removed.
- **`migrations/m260718_120000_add_deep_relation_names.php`** — seeds the new
  `relation_name` tokens; **must be run (`./yii migrate`)** for the new relations
  to get proper names (before it runs they degrade gracefully to the raw token).
- **Slovak** entries added in `messages/sk-SK/app-m.php` + `app-f.php`.
- **Tests**: `tests/unit/models/KinshipCalculusTest.php` (10 tests) and
  `KinshipResolverTest.php` (3 tests). Note: the unit suite needs
  `config/test_db.php` (currently absent from the repo — a dummy config is enough
  since the pure tests never hit the DB; the Yii `db` component connects lazily).

## 11. Lesson learned — a partner's child is inferred as your own child (2026-09-02)

**Symptom:** with `A`, `B = wife of A`, and `C = child of B` (no explicit A–C
relation), listing A's relations showed B but **not** C. The son silently
disappeared.

**Cause:** the BFS reached C only via `A --partner--> B --child--> C`, i.e.
`compose('partner', 'child')`. The `MARRIAGE` table had no such entry and
`partner` is not a `COORDS` (blood) token, so `compose()` returned `null` — the
correct signal for "not expressible, prune this path". Pruning was right in
general but wrong here: a spouse's child *is* expressible.

**Decision (deliberate, not just a null-fix):** the relation graph **cannot
distinguish** a couple's shared biological child from a step-child — both are
stored identically as `partner(A,B)` + `parent(B,C)`. We resolve the ambiguity
in favour of the common case: a **current** partner's child counts as your own
`child` (and, symmetrically, a parent's spouse as your own `parent`). So the
fix adds `MARRIAGE['partner']['child'] = 'child'` and
`MARRIAGE['parent']['partner'] = 'parent'` — **not** `step-child`/`step-parent`.

**Why this is safe:**
- Genuine step relations can still be entered **explicitly**; given relations
  always win and are never overridden by inference (they're in the `given` set
  and skipped by `traverse()`).
- **Ex-partners carry a distinct token** (`ex-partner`), which has no `MARRIAGE`
  rule, so an ex's child is deliberately **not** claimed.
- Because the path now yields the blood token `child`, deeper lineage through a
  spouse composes naturally via the coordinate arithmetic (a spouse's grandchild
  → `grandchild`). The old `step-child` token was a dead-end (not in `COORDS`),
  so it silently truncated those chains too.

**Takeaways for future edits to `compose()`:**
- `compose()` returning `null` drops the person entirely — before adding a
  `null` (prune) case, check you aren't hiding a real, nameable relation.
- New `MARRIAGE`/marriage-step entries should prefer a **blood token** as the
  result whenever the couple-shares-lineage assumption applies, so the
  coordinate algebra can keep extending the path; reserve `step-*`/`*-in-law`
  result tokens for relations that genuinely shouldn't deepen.
- **Code-only** change: computed relations are derived at read time and the
  son/daughter vocabulary already existed — **no migration or data change**.

Fixed on branch `dev`, commit `3caa5f8`.

Still open / future work:
- Extend the `MARRIAGE` table / step-relation handling if deeper in-law chains
  are wanted (currently pruned).
- Optionally generate great-/`pra-` names programmatically from the coordinate
  instead of seeding each token (see §7).
- Fix the `relation_pair` seed typos noted in `ARCHITECTURE.md` §4.
