<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 9-5-2019
 * Time: 17:52
 */

namespace App\Http\Handlers;


use App\Models\Headline\Headline;
use App\Models\WorkFunction\WorkFunction;
use Exception;
use Illuminate\Support\Facades\DB;

class HeadlinesHandler
{
    const TABLE = 'headlines';

    public function getHeadline(int $id, int $workFunctionId)
    {
        try {
            $results = DB::table(self::TABLE)
                ->where('id', $id)
                ->first();
            if ( $results === null ) {
                return response('Headline does not exist', 404);
            }
        } catch (\Exception $e) {
            return \response('HeadlinesHandler: There is something wrong with the database connection',500);
        }

        try {
            $headline = $this->makeHeadline($results, $workFunctionId);
        }catch (\Exception $e) {
            return \response('HeadlinesHandler: There is something wrong with the database connection',500);
        }

        return $headline;
    }

    public function postHeadlines(int $workFunctionId, array $newHeadlines)
    {
        $container = [];
        foreach ($newHeadlines as $headline) {
            try {
                $id = DB::table(self::TABLE)
                    ->insertGetId($headline);
            } catch (\Exception $e) {
                return \response($e->getMessage(),500);
            }
            array_push($container, $this->getHeadline($id, $workFunctionId));
        }
        return $container;
    }

    /**
     * @param Headline $headline
     * @param int $workFunctionId
     * @return int
     * @throws Exception
     */
    private function getOrder(Headline $headline, int $workFunctionId): int
    {
        try {
            $results = DB::table(WorkFunctionsHandler::MAIN_HAS_HEADLINE_TABLE)
                ->where('workFunctionId', $workFunctionId)
                ->where('headlineId', $headline->getId())
                ->first();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }

        return $results ? $results->order : 0;
    }

    /**
     * @param \stdClass $data
     * @param int $workFunctionId
     * @return Headline
     * @throws Exception
     */
    private function makeHeadline(\stdClass $data, int $workFunctionId): Headline
    {
        $headline = new Headline();

        $headline->setId($data->id);
        try {
            $headline->setOrder($this->getOrder($headline, $workFunctionId));
        }catch (\Exception $e) {
            throw new Exception($e->getMessage(),500);
        }
        $headline->setName($data->name);

        return $headline;
    }
}