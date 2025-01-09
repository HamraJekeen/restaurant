<!DOCTYPE html>
<html>
    <head>
    <meta http-equiv="refresh" content="0; url={{ route('home') }}">
    </head>
<body>
    <p>Please wait while you're redirected to the billing system...</p>
    <script>
        window.location.href = "{{ route('home') }}";
    </script>
    </body>
</html>
