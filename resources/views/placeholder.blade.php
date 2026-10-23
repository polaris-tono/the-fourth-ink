@extends('layouts.app')

@section('title', $title)

@section('content')
    <h1 class="font-serif text-3xl font-bold">{{ $title }}</h1>
    <p class="mt-3 text-ink-soft">
        Signed in as {{ auth()->user()->name }} ({{ auth()->user()->role->label() }}). This page is built in a later step.
    </p>
@endsection