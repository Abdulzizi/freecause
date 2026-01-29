@extends('layouts.legacy')

@section('title', $category->name . ' Petitions - Freecause')

@section('content')
<section class="py-5">
  <div class="container">

    <h1 class="mb-4">
      {{ $category->name }} Petitions
    </h1>

    <div class="row">
      @forelse ($petitions as $petition)
        <div class="col-md-4 mb-4">
          {{-- reuse your petition card partial --}}
          <div class="card h-100">
            <div class="card-body">
              <h5>{{ $petition->title }}</h5>
              <p class="text-muted">
                {{ Str::limit($petition->description, 120) }}
              </p>
              <a href="{{ url(app()->getLocale().'/petition/'.$petition->slug.'/'.$petition->id) }}">
                Read more
              </a>
            </div>
          </div>
        </div>
      @empty
        <p>No petitions found in this category.</p>
      @endforelse
    </div>

    {{ $petitions->links() }}

  </div>
</section>
@endsection
