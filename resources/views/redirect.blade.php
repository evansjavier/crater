<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <title>Redireccionando....</title>

        <script type="text/javascript"> window.location = "{!! $to !!}"; /* redirect */ </script>
    </head>
    <body>
        Redireccionando <a href="{!! $to !!}">Ir </a>
    </body>
</html>
