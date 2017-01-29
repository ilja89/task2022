@include('popup.header')

<div id="app">

    <popup-header></popup-header>

    <popup-navigation></popup-navigation>

    <popup-content></popup-content>

    <loader :visible="false"></loader>
</div>

<script src="/mod/charon/plugin/public/js/popup.js"></script>

@include('popup.footer')
