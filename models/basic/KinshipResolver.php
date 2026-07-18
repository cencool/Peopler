<?php

namespace app\models\basic;

use Yii;

/**
 * KinshipResolver — computes the implicit (non-given) relations of a person by
 * traversing the relation graph.
 *
 * The person graph is loaded once (owner-scoped, both directions) into an
 * adjacency map. A breadth-first search from the source person carries a
 * cumulative relation token (source -> current node); each edge is composed with
 * {@see KinshipCalculus::compose()}. BFS guarantees the first (shortest) path to
 * a person yields the most direct kinship term. A `visited` set and a depth cap
 * keep the search finite on dense family subgraphs.
 *
 * Output rows match the shape produced by Person::givenRelations() with
 * `relation_id = -1` marking a computed (non-editable) relation:
 *   [ relation_id, to_whom_id, relation_to_whom, relation ]
 *
 * The `relation` label is the gendered relation_name chosen by the *target's*
 * gender (the person the relation points to).
 *
 * See COMPUTED_RELATIONS.md for the design.
 */
class KinshipResolver
{
    /** Default maximum number of edges from the source person. */
    const DEFAULT_MAX_DEPTH = 4;

    /** @var int */
    private $personId;
    /** @var string owner whose subgraph is traversed */
    private $owner;
    /** @var string source person's gender ('m'|'f'|'?'), used to label output */
    private $sourceGender;
    /** @var int */
    private $maxDepth;

    public function __construct(
        int $personId,
        string $owner,
        string $sourceGender = '?',
        int $maxDepth = self::DEFAULT_MAX_DEPTH
    ) {
        $this->personId = $personId;
        $this->owner = $owner;
        $this->sourceGender = $sourceGender;
        $this->maxDepth = $maxDepth;
    }

    /**
     * Compute the source person's implicit relations from the database.
     *
     * @return array list of computed relation rows
     */
    public function compute(): array
    {
        return $this->traverse(
            $this->buildAdjacency(),
            $this->givenTargetIds(),
            $this->buildNameMap()
        );
    }

    /**
     * Pure BFS over an injected graph — no database access, so it is directly
     * unit-testable.
     *
     * @param array<int, array>                       $adjacency personId => list of edges
     *                                                {to, token, to_name, to_gender}
     * @param array<int, true>                        $given     person ids to skip (explicit relations)
     * @param array<string, array<string, string>>   $nameMap   [gender][token] => relation_name
     * @return array computed relation rows
     */
    public function traverse(array $adjacency, array $given, array $nameMap): array
    {
        $computed = [];
        $visited = [$this->personId => true];
        // Queue entries: [personId, pathToken, depth].
        $queue = [[$this->personId, KinshipCalculus::SELF, 0]];

        while ($queue) {
            list($currentId, $pathToken, $depth) = array_shift($queue);
            if ($depth >= $this->maxDepth) {
                continue;
            }
            foreach ($adjacency[$currentId] ?? [] as $edge) {
                $neighborId = $edge['to'];
                if (isset($visited[$neighborId])) {
                    continue;
                }
                $newToken = KinshipCalculus::compose($pathToken, $edge['token']);
                if ($newToken === null || $newToken === KinshipCalculus::SELF) {
                    // Not expressible (or resolves to self): don't extend this path.
                    continue;
                }
                // Shortest path wins: fix this person's relation now.
                $visited[$neighborId] = true;
                $queue[] = [$neighborId, $newToken, $depth + 1];

                if (!isset($given[$neighborId])) {
                    // compose() yields "neighbor is source's $newToken" (the label
                    // describes the neighbor). The app lists a person's relations
                    // as "this person is <relation> of <to_whom>", so we emit the
                    // inverse, gendered by the source person.
                    $outToken = KinshipCalculus::tokenComplement($newToken) ?? $newToken;
                    $computed[] = [
                        'relation_id' => -1,
                        'to_whom_id' => $neighborId,
                        'relation_to_whom' => $edge['to_name'],
                        'relation' => $this->tokenToRelationName($outToken, $this->sourceGender, $nameMap),
                    ];
                }
            }
        }

        return $computed;
    }

    /**
     * Load the visible relation graph in a single query and expand each stored
     * relation into two directed, token-labelled edges (forward + complement).
     *
     * Visibility mirrors {@see Person::find()}: `admin` sees every person (so
     * cross-owner relations are traversed), while a normal user is scoped to
     * their own people. Without this, an explicit relation linking two different
     * owners' persons would be dropped and its implied relations never computed.
     *
     * @return array<int, array>
     */
    private function buildAdjacency(): array
    {
        $sql = <<<SQL
        select pr.person_a_id as a_id, pr.person_b_id as b_id, rn.token as token_ab,
               concat(pa.surname, ' ', pa.name) as a_name,
               concat(pb.surname, ' ', pb.name) as b_name,
               pa.gender as a_gender, pb.gender as b_gender
        from person_relation pr
        join relation_name rn on rn.id = pr.relation_ab_id
        join person pa on pa.id = pr.person_a_id
        join person pb on pb.id = pr.person_b_id
        SQL;

        $params = [];
        if (Yii::$app->user->id !== 'admin') {
            $sql .= "\n        where pa.owner = :owner and pb.owner = :owner";
            $params[':owner'] = $this->owner;
        }
        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        $adjacency = [];
        foreach ($rows as $row) {
            // A stored relation (A, token_ab, B) means "A is token_ab of B"
            // (the label's gender matches A). So A plays role token_ab relative
            // to B: edge B -> A carries token_ab.
            $adjacency[$row['b_id']][] = [
                'to' => (int) $row['a_id'],
                'token' => $row['token_ab'],
                'to_name' => $row['a_name'],
                'to_gender' => $row['a_gender'],
            ];
            // The reverse (B relative to A) is the complement: edge A -> B.
            $reverse = KinshipCalculus::tokenComplement($row['token_ab']);
            if ($reverse !== null) {
                $adjacency[$row['a_id']][] = [
                    'to' => (int) $row['b_id'],
                    'token' => $reverse,
                    'to_name' => $row['b_name'],
                    'to_gender' => $row['b_gender'],
                ];
            }
        }
        return $adjacency;
    }

    /**
     * Ids of people the source already has an explicit relation to — these are
     * skipped when emitting computed relations.
     *
     * @return array<int, true>
     */
    private function givenTargetIds(): array
    {
        $sql = <<<SQL
        select person_b_id as other_id from person_relation where person_a_id = :id
        union
        select person_a_id as other_id from person_relation where person_b_id = :id
        SQL;
        $rows = Yii::$app->db->createCommand($sql, [':id' => $this->personId])->queryAll();
        $ids = [];
        foreach ($rows as $row) {
            $ids[(int) $row['other_id']] = true;
        }
        return $ids;
    }

    /**
     * @return array<string, array<string, string>> [gender][token] => relation_name
     */
    private function buildNameMap(): array
    {
        $rows = Yii::$app->db->createCommand(
            'select gender, token, relation_name from relation_name'
        )->queryAll();
        $map = [];
        foreach ($rows as $row) {
            // First relation_name wins for a given (gender, token).
            if (!isset($map[$row['gender']][$row['token']])) {
                $map[$row['gender']][$row['token']] = $row['relation_name'];
            }
        }
        return $map;
    }

    /**
     * Gendered relation_name for a token, falling back to the generic RELATIVE
     * label and finally to the raw token so a person is never dropped.
     */
    private function tokenToRelationName(string $token, string $gender, array $nameMap): string
    {
        if (isset($nameMap[$gender][$token])) {
            return $nameMap[$gender][$token];
        }
        if (isset($nameMap[$gender][KinshipCalculus::RELATIVE])) {
            return $nameMap[$gender][KinshipCalculus::RELATIVE];
        }
        return $token;
    }
}
