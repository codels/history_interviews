@extends('layouts.default')
@section('content')
    <a href="{{ url('/news') }}">News</a>
    News ({{ $newsId }}): <input id="news_text" type="text">
    <button id="news_save">save</button>
    <button id="news_delete">delete</button>

    <table id="comments" style="width: 100%;">
        <thead>
        <tr>
            <th>ID</th>
            <th>user_name</th>
            <th>comment</th>
            <th>date</th>
            <th>action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
        <tr>
            <th>ID</th>
            <th>user_name</th>
            <th>comment</th>
            <th>date</th>
            <th>action</th>
        </tr>
        </tfoot>
    </table>

    Text:<input id="comment_text" type="text"><br>
    UserName<input id="user_name" type="text"><br>
    <button id="comment_create">create comment</button>

    <script>
        var newsId = '{{ $newsId }}';
        var removeComment = function(id) {
            console.log('removing comment ' + id);
            $.ajax({
                url: '/api/news/' + newsId + '/comments/'+ id,
                type: 'DELETE',
                contentType: 'application/json',
                success: function() {
                    $('#comment_'+id).remove();
                }
            });
        };

        $(document).ready(function () {
            $.get('/api/news/' + newsId, {}, function(data) {
                console.log('get news:');
                console.log(data);
                $('#news_text').val(data.text);
            });

            $.get('/api/news/' + newsId + '/comments', {}, function(data) {
                if (data.length > 0) {
                    data.forEach(function (e) {
                        // XSS protection...
                        $("#comments tbody").append('<tr id="comment_'+e.id+'"><td>' + e.id + '</td><td>' + e.user_name.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
                            return '&#'+i.charCodeAt(0)+';';
                        }) + '</td><td>'+e.comment.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
                            return '&#'+i.charCodeAt(0)+';';
                        })+'</td><td>' + e.created_at + '</td><td><button onclick="removeComment('+e.id+')">Remove</button></td></tr>');
                    })
                }
            });

            $('#news_save').click(function() {
                console.log('news saved');
                $.ajax({
                    url: '/api/news/' + newsId,
                    type: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify({ text: $('#news_text').val() }),
                    success: function() {
                        alert('saved');
                    }
                });
            });

            $('#news_delete').click(function() {
                console.log('news deleting');
                $.ajax({
                    url: '/api/news/' + newsId,
                    type: 'DELETE',
                    contentType: 'application/json',
                    success: function() {
                        alert('deleted');
                    }
                });
            });

            $('#comment_create').click(function() {
                console.log('comment creating');
                $.ajax({
                    url: '/api/news/' + newsId + '/comments',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ comment: $('#comment_text').val(), user_name: $('#user_name').val() }),
                    success: function(e) {
                        alert('created');
                        // XSS protection...
                        $("#comments tbody").append('<tr id="comment_'+e.id+'"><td>' + e.id + '</td><td>' + e.user_name.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
                            return '&#'+i.charCodeAt(0)+';';
                        }) + '</td><td>'+e.comment.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
                            return '&#'+i.charCodeAt(0)+';';
                        })+'</td><td>' + e.created_at + '</td><td><button onclick="removeComment(\'+e.id+\')">Remove</button></td></tr>');
                    }
                });
            });
        });
    </script>
@stop
