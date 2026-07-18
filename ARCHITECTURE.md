# Peopler — Architecture & Developer Notes

> Reference document for development sessions. Complements the user-facing
> setup guide in `README.md`. Last reviewed against commit `eaad2c6`.

## 1. What this project is

Peopler is a **Yii2 (basic template) PHP backend** that maintains a database of
people together with their photos, personal details, attachment files, notes,
items, and — most importantly — **relations to one another**.

It serves two front ends:

1. **Server-rendered web app** — classic Yii2 MVC (`controllers/`, `models/`,
   `views/`), used from a browser.
2. **REST API** under `modules/v1/` — consumed by an **Android app written in
   Flutter**. The API uses HTTP Basic auth + CORS and returns JSON.

Both front ends share the same domain models in `models/basic/`.

## 2. Tech stack

| Concern        | Choice                                             |
|----------------|----------------------------------------------------|
| Framework      | Yii2 basic app template (`yiisoft/yii2 ~2.0.14`)   |
| PHP            | `>=5.6` declared; codebase uses 7.x idioms         |
| DB             | MySQL / MariaDB                                     |
| Images         | `yiisoft/yii2-imagine`, `cropperjs` for photo crop |
| Auth (web)     | Session + `AccessControl` (`roles: ['@']`)         |
| Auth (API)     | `HttpBasicAuth` + `access_token` column            |
| Tests          | Codeception (`tests/`)                             |
| Deploy         | `deploy_web.sh`, `docker-compose.yml`, Vagrant     |

## 3. Directory map

```
controllers/           Web controllers (Person, Relation, Photo, Attachment, Item, Site)
models/basic/          Shared ActiveRecord models + search/form models
modules/v1/            REST API module for the Flutter app
  controllers/         API controllers (Person, Relation, Attachment, Photo, Item, PersonDetail)
  models/              API-specific model variants (Person, PersonDetail)
views/                 Server-rendered PHP views (person/, relation/, photo/, ...)
migrations/            DB schema (see m211004_184234_create_db_tables.php)
config/                web.php, db.php (gitignored, copy from db_example.php), params.php
commands/              Console commands (e.g. user/create-user)
db/peopler.sql         SQL dump of the schema/seed
messages/              i18n: app.php, app-m.php, app-f.php + sk-SK/ (Slovak)
uploads/               User-uploaded photos & attachments
documentation/api/     Generated phpDoc (not hand-written)
```

## 4. Data model (core tables)

Defined in `migrations/m211004_184234_create_db_tables.php`.

### `person`
`id, surname, name, place, gender('m'|'f'|'?'), owner`
- **Ownership** is enforced by overriding `Person::find()`
  (`models/basic/Person.php:35`): non-admin users only see rows where
  `owner = current user id`. `admin` sees everything.

### `person_detail`  (1:1 with person)
`marital_status, maiden_name, note, address`

### `relation_name`  — the relationship vocabulary
`id, gender('m'|'f'|'?'), relation_name, token`
- `relation_name` is the **gendered display label** (e.g. `mother`, `son`,
  `grandfather`).
- `token` is the **gender-neutral canonical relation** (e.g. `parent`, `child`,
  `grandparent`). Multiple gendered names collapse to one token
  (`mother`/`father` → `parent`).
- Unique on `(gender, relation_name)`.
- Tokens in seed data: `parent, child, sibling, partner, grandparent,
  grandchild, uncle, nephew, cousin, parent-in-law, child-in-law,
  sibling-in-law, godmother, godfather, friend, classmate, colleague,
  acquaintance`.

### `person_relation`  — the explicit (given) edges
`id, person_a_id, relation_ab_id → relation_name.id, person_b_id`
- Unique on `(person_a_id, relation_ab_id, person_b_id)`.
- Semantics: the stored row means **"person_a is person_b's `relation_ab`"** —
  the relation label describes **person_a** and its gender matches **A's** gender
  (e.g. row `(A=3 female, daughter, B=91)` = "3 is 91's daughter", so 91 is 3's
  parent). Verify direction against real data before relying on it — it is the
  opposite of the naive "B is A's relation" reading and a past bug in
  `KinshipResolver::buildAdjacency()` came from getting it backwards.
- Only **one direction is stored**; the reverse is derived on the fly via
  `relation_pair` (the complement).

### `relation_pair`  — complement/inverse lookup
`gender_a, relation_ab, gender_b, relation_ba`
- Maps a relation to its inverse given both genders, e.g.
  `(m, son, f, mother)` means "if A(m) is B(f)'s son, then B is A's mother".
- Used by `RelationName::relationComplement()` and friends to compute the
  reverse label when displaying / storing relations.
- ⚠️ **Known typos in seed data** that silently break complement lookups for
  those specific relations: `'granddaugther'` (line ~184) and `'siter-in-law'`
  (line ~213). Worth fixing when touching relations.

### Other tables
`person_attachment` (files), `items` (person_id, item, item_link),
`person_photo` (added in `m230714_...`), `user` (user_id, pwd_hash;
`access_token` added in `m240309_...`).

## 5. How relations flow (read path)

`Person::relations()` (`models/basic/Person.php:226`) is the public entry point.
It returns:

```
relations() = givenRelations()  ⊕  computedRelations()   (then i18n-translated)
```

Each relation item is a normalized assoc array:
`{ relation_id, relation, to_whom_id, relation_to_whom, a_owner, b_owner }`
meaning **"this person is `relation` of `to_whom_id`"** (e.g. person 3's item
`{relation: daughter, to_whom_id: 91}` = "3 is the daughter of 91").

- **`givenRelations()`** — union of:
  - `getRelationsFromPerson()` — raw SQL, rows where this person is `person_a`;
    returns `relation_ab` as-is (this person A **is** `relation_ab` of B).
  - `getRelationsToPerson()` — raw SQL, rows where this person is `person_b`;
    returns the **complement** label via `relation_pair` (this person B **is**
    the complement of A).
  - **Owner isolation:** both queries append `and pb.owner = :owner` for
    non-admin viewers (admin unfiltered), so a normal user only sees relations
    whose *other* endpoint they also own. Without this, an admin-created
    cross-owner relation would leak the other owner's person name into a
    non-admin's list (fixed 2026-07-18).

  Note: `KinshipResolver` does **not** consume `givenRelations()`; it rebuilds the
  graph straight from `person_relation` (see `COMPUTED_RELATIONS.md`). It only
  uses the set of directly-related person ids to skip already-explicit relations.
- **`computedRelations()`** — implicit relations inferred by composing given
  relations (see `COMPUTED_RELATIONS.md`). Computed items are flagged with
  `relation_id = -1`.

### Consumers
- Web: `views/person/personView.php:113` and `personUpdate.php` render
  relations; `relation_id == -1` (computed) items are shown in `<code>` tags and
  are **not editable/deletable** (they don't exist as rows).
- API: `modules/v1/controllers/RelationController::actionView($id)` wraps
  `relations()` in an `ArrayDataProvider` with sort + `like` filtering.

## 6. Relations write path (web)

`controllers/RelationController::actionAddRelation()` — user picks person B and a
relation label; the controller stores A→B and relies on `relation_pair` for the
inverse. Duplicate and self-relation guards are applied.
`actionUpdate` / `actionDelete` operate on a single `person_relation` row with an
ownership check (`PersonRelation::checkOwnership()`).

## 7. Auth & multi-tenancy notes

- Every `Person` query is auto-scoped to `owner` (`Person::find()`): a normal
  user only sees their own people, while **`admin` sees all** (the scope is
  skipped). `KinshipResolver::buildAdjacency()` reproduces exactly this rule —
  admin's relation graph is unscoped so cross-owner relations are traversed; a
  normal user's graph is limited to their own people. (Explicit relations *can*
  span owners in the data, so scoping the whole graph to a single owner would
  drop those edges — that was a real bug, see `COMPUTED_RELATIONS.md` §8.)
- API `access_token` lives on the `user` table (migration
  `m240309_080025_add_access_token.php`). API auth is `HttpBasicAuth` →
  `User::findIdentity`/`validateAuthKey`, so `Yii::$app->user->id` correctly
  identifies the calling user (admin/demo/…) in API actions too.
- **Relation ownership** = a relation is "owned" only when the user owns **both**
  its persons (admin owns all). `PersonRelation::checkOwnership()` encodes this
  (reads owners directly, not via the owner-scoped `Person` relations). It gates
  the web `RelationController` update/delete and the API
  `RelationController::actionViewRelation`/`actionDelete` (404 on denial). The API
  create/update also validate both person ids via the owner-scoped
  `Person::findOne`.
- **Delete cascade is intentional/uncontrolled**: `person_relation` FKs on both
  `person_a_id` and `person_b_id` are `ON DELETE CASCADE`, so deleting a person
  removes every relation touching it — *including* admin's cross-owner rows. By
  product decision there is **no** delete guard: an owner may delete their own
  person and the cascade takes the linked relations with it. (`Undelete` snapshots
  relations to the deleter's session, but it's single-slot and per-user — not a
  reliable cross-user backup.)

## 8. Gotchas / tech debt worth knowing

- `computedRelations()` is the main area under active improvement — see
  `COMPUTED_RELATIONS.md` for a full analysis and upgrade proposal.
- Heavy per-row `RelationName::find()` queries inside relation loops (N+1).
- `relation_pair` seed typos (above).
- `gender = '?'` (unknown) exists but relation vocabulary/pairs mostly assume
  `m`/`f`; unknown-gender people can fall out of complement/token lookups.
- Vocabulary has **no** great-grandparents, grand-nephews, or cousin-degrees, so
  deep computed relations can produce structurally-correct results that have no
  name/translation.
