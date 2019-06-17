@extends('layouts.default')
@section('content')
    <a href="{{ url('/') }}">Home Page</a>
    <table id="news" style="width: 100%;">
        <thead>
        <tr>
            <th>ID</th>
            <th>text</th>
            <th>created</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
        <tr>
            <th>ID</th>
            <th>text</th>
            <th>created</th>
        </tr>
        </tfoot>
    </table>

    <input id="news_text" type="text"><button id="news_create">Create news</button>

    <script>
        $(document).ready(function () {
            $.get('/api/news', {}, function(data) {
                if (data.length > 0) {
                    data.forEach(function (e) {
                        // XSS protection...
                        $("#news tbody").append('<tr id="news_'+e.id+'"><td><a href="{{ url('/news') }}/'+e.id+'">' + e.id + '</a></td><td>' + e.text.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
                            return '&#'+i.charCodeAt(0)+';';
                        }) + '</td><td>' + e.created_at + '</td></tr>');
                    })
                }
            });

            $('#news_create').click(function() {
                console.log('news creating');
                $.ajax({
                    url: '/api/news',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ text: $('#news_text').val() }),
                    success: function() {
                        alert('created');
                    }
                });
            });
        });
    </script>
@stop