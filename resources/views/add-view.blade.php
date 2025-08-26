@extends('layouts.app')

@section('content')
    @if(auth()->user()->add_view_permission == 1)
        <section>
            <div class="container bg-white px-4 log_section pb-5 pt-lg-4">
                <h4 class="font-weight-bold">Add Views</h4>

                <form action="{{ route('users.resetViews', auth()->user()->id) }}" method="POST" class="form-group">
                    @csrf
                    {{-- <div id="viewAdd"></div> --}}
                    {{-- <input type="hidden" name="user" value="{{ auth()->user()->id }}"> --}}
                    <input type="submit" class="btn log_btn mt-2" value="Add">
                </form>

                {{-- <div id="output2"></div> --}}
            </div>
        </section>
    @else
        <section class="py-4">
            <div class="container bg-white px-4 py-5 shadow-sm rounded">
                <h4 class="font-weight-bold ">Pls contact 5660 6352 to add views</h4>
            </div>
        </section>
    @endif


    <section class="bg-white">
    <footer>
        <div class="container social_icon text-center">
        <hr class="font-weight-bold">
        <small class="text-muted">Copyright 2024</small>
        </div>
    </footer>
    </section>

@endsection