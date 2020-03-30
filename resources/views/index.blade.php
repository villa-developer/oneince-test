@extends('layouts.app')


@section('content')
    <home-component :user="{{ Auth::user() }}" :account="{{ (Auth::user()->account) ? Auth::user()->account : 'null' }}"></home-composnent>
@endsection