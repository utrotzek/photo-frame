<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>welcome.</title>
    <meta name="description" content="Login Page">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            margin: 0px;
            padding: 0px;
            cursor: none;
        }
        #all_slides{
            position: relative;
            height: 100vh;
            padding: 0px;
            margin: 0px;
            list-style-type: none;
        }

        .slide{
            position: absolute;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            opacity: 0;
            z-index: 1;
            -webkit-transition: opacity 10s;
            -moz-transition: opacity 10s;
            -o-transition: opacity 10s;
            transition: opacity 10s;
        }

        .active{
            opacity: 1;
            z-index: 2;
        }

        .slide{
            font-size: 40px;
            padding: 40px;
            box-sizing: border-box;
            background: #333;
            color: #fff;
            background-size: cover;
        }

        .slide:nth-of-type(1){
            background-image: url('./images/1.jpg');
            background-size: cover;
            background-position: 50% 50%;
            background-repeat: no-repeat;
        }

        .slide:nth-of-type(2){
            background-image: url('./images/2.jpg');
            background-size: cover;
            background-position: 50% 50%;
            background-repeat: no-repeat
        }

        .slide:nth-of-type(3){
            background-image: url('./images/3.jpg');
            background-size: cover;
            background-position: 50% 50%;
            background-repeat: no-repeat
        }

        .controls{
            display: none;
        }

        .controls{
            display: inline-block;
            position: relative;
            top: 1rem;
            right: .5rem;
            border: none;
            outline: none;
            font-size: 20px;
            cursor: pointer;
            border: 2px solid #fff;
            border-radius: 1.5rem;
            background: gold;
            width: 3rem;
            height: 3rem;
            margin-left: .5rem;
        }

        .controls:hover,
        .controls:focus{
            background: #eee;
            color: #333;
        }

        .container{
            position: relative;
        }

        .buttons{
            position: absolute;
            right: .5rem;
            top: 0px;
            z-index: 10;
            font-size: 0px;
        }
    </style>
</head>
<body>

<div id="app">
    <ul id="all_slides">
        <li class="slide active"></li>
        <li class="slide"></li>
        <li class="slide"></li>
        ...
    </ul>

    <div class="buttons">
        <button class="controls" id="previous"><i class="far fa-arrow-alt-circle-left"></i></button>
        <button class="controls" id="pause"><i class="far fa-pause-circle"></i></button>
        <button class="controls" id="next"><i class="far fa-arrow-alt-circle-right"></i></button>
    </div>

</div>


<!-- Bootstrap core JavaScript-->
<script src="{{ mix('js/app.js') }}"></script>
<script src="{{ mix('js/main.js') }}"></script>
</body>
</html>
