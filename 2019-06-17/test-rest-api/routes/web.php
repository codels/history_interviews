<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// Temp interface for tests
$router->get('/', function () use ($router) { return view('pages.index'); });
$router->get('/news', function () use ($router) { return view('pages.news'); });
$router->get('/news/{news_id}', function ($newsId) use ($router) { return view('pages.news_info', compact('newsId')); });

// Rest API

// news
$router->get('/api/news', 'Api\\NewsController@show'); // ok
$router->get('/api/news/{news_id}', 'Api\\NewsController@get'); // ok
$router->delete('/api/news/{news_id}', 'Api\\NewsController@remove'); // ok
$router->put('/api/news/{news_id}', 'Api\\NewsController@update'); // ok
$router->post('/api/news', 'Api\\NewsController@create'); // ok

// comments for news
$router->get('/api/news/{news_id}/comments', 'Api\\NewsCommentController@show'); // ok
$router->delete('/api/news/{news_id}/comments/{comment_id}', 'Api\\NewsCommentController@remove'); // ok
$router->post('/api/news/{news_id}/comments', 'Api\\NewsCommentController@create'); // ok