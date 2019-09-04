<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 23-5-2019
 * Time: 14:46
 */

namespace App\Http\Controllers\Cache;


use App\Http\Handlers\CacheHandler;
use Exception;
use Illuminate\Http\Request;

class CacheController
{
    /**
     * @var CacheHandler
     */
    private $cacheHandler;

    public function __construct(CacheHandler $cacheHandler)
    {
        $this->cacheHandler = $cacheHandler;
    }

    public function getCache(Request $request, int $id = null)
    {
        try {

            if ($request->input('hash') && $id) {
                return $this->cacheHandler->checkCacheHash($id, $request->input('hash'));
            }

            if ($request->input('name') && $request->input('url')) {
                return $this->cacheHandler->getCacheByNameAndUrl($request->input('name'), $request->input('url'));
            }
        } catch (Exception $e) {
            return response($e->getMessage(), 200);
        }


        return response('There are no params given', 400);
    }

    public function postCache(Request $request)
    {
        return $this->cacheHandler->createNewCache($request->post());
    }

    public function updateCache(Request $request, int $id)
    {
        return $this->cacheHandler->updateCache($this->cacheHandler->getCacheItem($id));
    }

}