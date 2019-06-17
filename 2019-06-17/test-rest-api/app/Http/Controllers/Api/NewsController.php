<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\News;

class NewsController extends Controller
{
    private $_request;

    public function __construct(Request $request)
    {
        $this->_request = $request;
    }

    public function show()
    {
        $build = News::orderByRaw('news.id DESC');

        $pagination = false;
        if($this->_request->has('pagination')) {
            $pagination = filter_var($this->_request->get('pagination'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($pagination) {
            $limit = 3;
            $page = 1;
            if ($this->_request->has('page_limit')) {
                $limit = (int)$this->_request->get('page_limit');
            }
            if ($this->_request->has('page_current')) {
                $page = (int)$this->_request->get('page_current');
            }
            $info = $build->paginate($limit, ['*'], 'page', $page);
        }

        $news = $build->get();

        $result = [];
        foreach ($news as $singleNews) {
            if ($singleNews instanceof News) {
                $result[] = $singleNews->toArray();
            }
        }

        $response = response($result);

        if ($pagination && isset($info)) {
            $response->header('pagination-count', $info->count());
            $response->header('pagination-per-page', $info->perPage());
            $response->header('pagination-current-page', $info->CurrentPage());
            $response->header('pagination-last-page', $info->lastPage());
            $response->header('pagination-total', $info->total());
        }

        return $response;
    }

    public function get($newsId)
    {
        $news = News::find($newsId);
        if ($news instanceof News) {
            return response($news->toArray());
        } else {
            return response(['error' => 'news_not_found'], 404);
        }
    }

    public function remove($newsId)
    {
        $news = News::find($newsId);
        if ($news instanceof News) {
            if ($news->delete()) {
                return response('{}');
            } else {
                return response(['error' => 'cannot_remove_news'], 400);
            }
        } else {
            return response(['error' => 'news_not_found'], 404);
        }
    }

    public function update($newsId)
    {
        $news = News::find($newsId);

        if (!($news instanceof News)) {
            return response(['error' => 'not_found'], 404);
        }

        if (!$this->_request->has('text')) {
            return response(['error' => 'incorrect_query'], 400);
        }

        $news->text = (string)$this->_request->get('text');

        $news->save();

        $news = News::find($news->id);
        return response($news instanceof News ? $news->toArray() : null);
    }

    public function create()
    {
        $newsInfo = [
            'text' => (string)$this->_request->get('text'),
        ];

        if (empty($newsInfo['text'])) {
            return response(['error' => 'text_is_empty'], 400);
        }

        $news = News::create($newsInfo);

        $news = News::find($news->id);
        return response($news instanceof News ? $news->toArray() : null);
    }
}
