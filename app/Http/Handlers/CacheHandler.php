<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 23-5-2019
 * Time: 14:48
 */

namespace App\Http\Handlers;


use App\Models\CacheItem\CacheItem;
use Illuminate\Support\Facades\DB;

class CacheHandler
{
    const TABLE = 'cache';

    public function getCacheItem(int $id)
    {
        try {
            $result = DB::table(self::TABLE)
                ->where('id', $id)
                ->first();
            if ( $result === null ) {
                return [];
            }
        } catch (\Exception $e) {
            return \response('CacheHandler: There is something wrong with the database connection',500);
        }

        return $this->makeCache($result);
    }

    public function getCacheByNameAndUrl(string $name, string $url)
    {
        try {
            $result = DB::table(self::TABLE)
                ->where('name', $name)
                ->where('url', $url)
                ->first();
            if ( $result === null ) {
                $data = ['name' => $name, 'url' => $url];
                return $this->makeCache($data);
            }
        } catch (\Exception $e) {
            return \response('CacheHandler: There is something wrong with the database connection',500);
        }

        return $this->makeCache($result);
    }

    public function checkCacheHash($id, $hash)
    {
        try {
            $results = DB::table(self::TABLE)
                ->where('id', $id)
                ->where('hash', $hash)
                ->first();
            if ( $results === null ) {
                ['sameHash' => false];
            }
        } catch (\Exception $e) {
            return \response('ChaptersHandler: There is something wrong with the database connection',500);
        }

        return ['sameHash' => true];
    }

    public function createNewCache($postData)
    {
        $values = $postData;
        $values['hash'] = bin2hex(random_bytes(4));
        try {
            $id = DB::table(self::TABLE)
                ->insertGetId($values);
        } catch (\Exception $e) {
            return \response('ChaptersHandler: There is something wrong with the database connection',500);
        }

        return $this->getCacheItem($id);
    }

    /**
     * Only update the hash value.
     * @param CacheItem $cache
     * @return CacheItem|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function updateCache(CacheItem $cache)
    {
        $values = ['hash' => bin2hex(random_bytes(4))];
        try {
            DB::table(self::TABLE)
                ->where('id', $cache->getId())
                ->update($values);
        } catch (\Exception $e) {
            return \response('ChaptersHandler: There is something wrong with the database connection',500);
        }
        $cache->setHash($values['hash']);
        return $cache;
    }

    private function makeCache($data): CacheItem
    {
        $cacheItem = new CacheItem();
        $cacheItem->setId($data->id);
        $cacheItem->setUrl($data->url);
        $cacheItem->setHash($data->hash);

        return $cacheItem;
    }

}