<header class="site-header">
    <div class="container header-inner">

        <a class="brand" href="{{ route('home') }}">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Markham Infiniti – Inspired Performance" width="200" height="190">
        </a>

        <button class="menu-toggle d-lg-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#headerMenu" aria-controls="headerMenu" aria-expanded="false"
                aria-label="Toggle navigation">&#9776;</button>

        <div class="header-menu collapse d-lg-block" id="headerMenu">

            {{-- top bar: address, phones, hours, social --}}
            <div class="topbar">
                <div class="topbar-info">
                    <span class="topbar-item">
                        <img src="{{ asset('assets/images/location.png') }}" alt="" width="13" height="18">
                        4340 Hwy 7 E, Markham, ON L3R 1L9
                    </span>
                    <a class="topbar-item" href="tel:+19057520881">
                        <img src="{{ asset('assets/images/call.png') }}" alt="" width="13" height="18">
                        (905) 752-0881
                    </a>
                    <a class="topbar-item" href="tel:+18667981346">
                        <img src="{{ asset('assets/images/toll-free.png') }}" alt="" width="19" height="20">
                        Sales : (866) 798-1346
                    </a>

                    <div class="dropdown hours-wrap">
                        <button class="hours-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Dealership Hours
                        </button>
                        <ul class="dropdown-menu hours-menu">
                            <li><span>Mon – Thu</span> <span>9:00 am – 8:00 pm</span></li>
                            <li><span>Friday</span> <span>9:00 am – 6:00 pm</span></li>
                            <li><span>Saturday</span> <span>9:00 am – 5:00 pm</span></li>
                            <li><span>Sunday</span> <span>Closed</span></li>
                        </ul>
                    </div>
                </div>

                <div class="social">
                    <a href="#" aria-label="Facebook"><img src="{{ asset('assets/images/fb.png') }}" alt=""></a>
                    <a href="#" aria-label="Twitter"><img src="{{ asset('assets/images/tw.png') }}" alt=""></a>
                    <a href="#" aria-label="YouTube"><img src="{{ asset('assets/images/yt.png') }}" alt=""></a>
                </div>
            </div>

            {{-- main navigation --}}
            <nav class="mainnav" aria-label="Main navigation">
                <ul>
                    <li><a href="{{ route('home') }}" class="active">Home</a></li>
                    <li class="has-sub"><a href="#">New Vehicles</a></li>
                    <li class="has-sub"><a href="#">Used Vehicles</a></li>
                    <li class="has-sub"><a href="#">Service</a></li>
                    <li class="has-sub"><a href="#">Parts</a></li>
                    <li class="has-sub"><a href="#">Financial Services</a></li>
                    <li class="has-sub"><a href="#">Contact Us</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>
