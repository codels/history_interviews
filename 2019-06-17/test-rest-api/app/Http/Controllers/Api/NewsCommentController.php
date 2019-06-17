<?php

namespace App\Http\Controllers\Api;

use App\NewsComment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\News;

class NewsCommentController extends Controller
{
    private $_request;

    public function __construct(Request $request)
    {
        $this->_request = $request;
    }

    public function show($newsId)
    {
        $build = NewsComment::orderByRaw('news_comments.id DESC');

        $pagination = false;
        if($this->_request->has('pagination')) {
            $pagination = filter_var($this->_request->get('pagination'), FILTER_VALIDATE_BOOLEAN);
        }

        $build->where([
            ['news_id', '=', (int)$newsId]
        ]);

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

        $comments = $build->get();

        $result = [];
        foreach ($comments as $comment) {
            if ($comment instanceof NewsComment) {
                $result[] = $comment->toArray();
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

    public function remove($newsId, $commentId)
    {
        $comment = NewsComment::find($commentId);
        if ($comment instanceof NewsComment && (int)$comment->news_id === (int)$newsId) {
            if ($comment->delete()) {
                return response(['success' => 'comment_removed']);
            } else {
                return response(['error' => 'cannot_remove_comment'], 400);
            }
        } else {
            return response(['error' => 'comment_not_found'], 404);
        }
    }

    public function create($newsId)
    {
        $commentInfo = [
            'comment' => (string)$this->_request->get('comment'),
            'user_name' => (string)$this->_request->get('user_name'),
            'news_id' => (int)$newsId,
        ];

        if (!$commentInfo['news_id']) {
            return response(['error' => 'news_not_found'], 404);
        }

        $news = News::find($commentInfo['news_id']);
        if (!($news instanceof News)) {
            return response(['error' => 'news_not_found'], 404);
        }

        $comment = NewsComment::create($commentInfo);

        return response($comment instanceof NewsComment ? $comment->toArray() : null);
    }
}
