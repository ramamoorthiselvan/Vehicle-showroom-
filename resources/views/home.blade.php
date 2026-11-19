@extends('layouts.app')

@section('content')

    {{-- ================= Hero / banner ================= --}}
    <section class="hero" aria-label="Featured vehicle">
        <img src="{{ asset('assets/images/banner.png') }}"
             alt="2015 Infiniti QX70 – starting at $53,500" width="1300" height="668">

        {{-- slider position indicator from the design (only one banner was supplied) --}}
        <div class="hero-dots" aria-hidden="true">
            <span class="on"></span><span></span><span></span><span></span><span></span>
        </div>
    </section>

    {{-- ================= Welcome ================= --}}
    <section class="welcome">
        <div class="container">
            <h1 class="section-title">Welcome to Markham Infiniti</h1>
            <p>
                Markham Infiniti is a family owned business in Toronto that has been servicing the GTA market since
                Infiniti was introduced. Agincourt Infiniti was one of the original fifteen Infiniti dealers appointed
                in Canada in 1990, and in October 2007 we relocated to Unionville, Ontario under our new name
                &ldquo;Markham Infiniti&rdquo;.
            </p>
        </div>
    </section>

    {{-- ================= Quick links ================= --}}
    <section class="quick-links" aria-label="Quick links">
        <div class="container">
            <div class="quick-row">
                <span class="quick-wave" aria-hidden="true"></span>

                <a href="#" class="quick-item">
                    <span class="q-circle">
                        <span class="q-icon">
                            <img class="off" src="{{ asset('assets/images/cta_01.png') }}" alt="">
                            <img class="on" src="{{ asset('assets/images/new_white.png') }}" alt="">
                        </span>
                        <span class="q-label">Shop New<br>Vehicles</span>
                    </span>
                    <img class="q-shadow" src="{{ asset('assets/images/shadow.png') }}" alt="">
                </a>

                <span class="quick-sep" aria-hidden="true"></span>

                <a href="#" class="quick-item">
                    <span class="q-circle">
                        <span class="q-icon">
                            <img class="off" src="{{ asset('assets/images/cta_02.png') }}" alt="">
                            <img class="on" src="{{ asset('assets/images/used_white.png') }}" alt="">
                        </span>
                        <span class="q-label">Shop Used<br>Vehicles</span>
                    </span>
                    <img class="q-shadow" src="{{ asset('assets/images/shadow.png') }}" alt="">
                </a>

                <span class="quick-sep" aria-hidden="true"></span>

                <a href="#" class="quick-item">
                    <span class="q-circle">
                        <span class="q-icon">
                            <img class="off" src="{{ asset('assets/images/cta_03.png') }}" alt="">
                            <img class="on" src="{{ asset('assets/images/book_white.png') }}" alt="">
                        </span>
                        <span class="q-label">Service</span>
                    </span>
                    <img class="q-shadow" src="{{ asset('assets/images/shadow.png') }}" alt="">
                </a>

                <span class="quick-sep" aria-hidden="true"></span>

                <a href="#" class="quick-item">
                    <span class="q-circle">
                        <span class="q-icon">
                            <img class="off" src="{{ asset('assets/images/cta_04.png') }}" alt="">
                            <img class="on" src="{{ asset('assets/images/special_white.png') }}" alt="">
                        </span>
                        <span class="q-label">Specials</span>
                    </span>
                    <img class="q-shadow" src="{{ asset('assets/images/shadow.png') }}" alt="">
                </a>
            </div>
        </div>
    </section>

    {{-- ================= Model showroom (from the database) ================= --}}
    <section class="showroom" aria-label="Model showroom">
        <div class="container">
            <h2 class="section-title">Model Showroom</h2>
        </div>

        <div class="showroom-band">
            @if ($showroomModels->isEmpty())
                <p class="showroom-empty mb-0">New models are coming soon. Please check back shortly.</p>
            @else
                <div class="container">
                    <div class="showroom-frame">
                        <button type="button" class="showroom-nav prev" id="showroomPrev" aria-label="Previous models"></button>

                        <div class="showroom-viewport">
                            <div class="showroom-track" id="showroomTrack">
                                @foreach ($showroomModels as $model)
                                    <article class="model-card">
                                        <h3 class="model-title" title="{{ $model->title }}">{{ $model->title }}</h3>

                                        <div class="model-img-wrap">
                                            @if ($model->image_url)
                                                <img src="{{ $model->image_url }}" alt="{{ $model->title }}" loading="lazy">
                                            @endif
                                        </div>

                                        <p class="model-price">Starting at {{ $model->formatted_price }}</p>

                                        <a href="#" class="model-plus" aria-label="View details for {{ $model->title }}"></a>
                                    </article>
                                @endforeach
                            </div>
                        </div>

                        <button type="button" class="showroom-nav next" id="showroomNext" aria-label="Next models"></button>
                    </div>
                </div>
            @endif
        </div>
    </section>

@endsection
