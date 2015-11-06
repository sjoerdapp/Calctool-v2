<header id="topNav" class="topHead">
	<div class="container">

		<button class="btn btn-mobile" data-toggle="collapse" data-target=".nav-main-collapse">
			<i class="fa fa-bars"></i>
		</button>

		<a class="logo" href="/">
			<img src="/images/logo2.png" width="200px" alt="Calctool" />
			<sup style="color: #719E00;font-weight: 100;">Beta</sup>
		</a>

		<div class="navbar-collapse nav-main-collapse collapse pull-right">
			<nav class="nav-main mega-menu">
				<ul class="nav nav-pills nav-main scroll-menu" id="topMain">
					<li>
						<a href="/">Home</a>
					</li>
					@if (0)
					<li>
						{{ HTML::link('help', 'Help') }}
					</li>
					@endif
					@if (Auth::check())
					<li>
						<a href="/myaccount">Mijn account</a>
					</li>
					@endif
					@if (0)
					<li>
						{{ HTML::link('about', 'Over ons') }}
					</li>
					@endif
					@if (Auth::check() && Auth::user()->isAdmin())
					<li>
						<a href="/admin">Admin CP</a>
					</li>
					@endif
					<li class="active">
					<?php
						if (Auth::check()) {
							$swap_session = Cookie::get('swpsess');
							if ($swap_session) {
								echo '<a href="/admin/switch/back">Terugkeren</a>';
							} else {
								Auth::user()->touch();
								echo '<a href="/logout">Uitloggen</a>';
							}
						} else {
							echo '<a href="/login">Login</a>';
						}
					?>
					</li>
					@if (0)
					<li class="search">
						<form method="get" action="#" class="input-group pull-right">
							<input type="text" class="form-control" name="k" id="k" value="" placeholder="Zoeken">
							<span class="input-group-btn">
								<button class="btn btn-primary notransition"><i class="fa fa-search"></i></button>
							</span>
						</form>
					</li>
					@endif
				</ul>
			</nav>
		</div>

	</div>
</header>