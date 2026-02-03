<?php
// Initialize the session
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'header.php'?>
		<!-- Ambient Background Orb -->
		<div class="ambient-orb" style="position: fixed; top: -10%; left: 50%; transform: translateX(-50%); width: 800px; height: 800px; background: radial-gradient(circle, rgba(168, 85, 247, 0.15) 0%, transparent 70%); z-index: -1; filter: blur(60px);"></div>

		<!-- Hero-area -->
		<div class="hero-area section">

			<!-- Backgound Image -->
			<div class="bg-image bg-parallax overlay" style="background-image:url(./img/bgc2.jpg); " ></div>
			<!-- /Backgound Image -->

			<div class="container">
				<div class="row" >
					<div class="col-md-10 col-md-offset-1 text-center">
						<ul class="hero-area-tree">
							<li><a href="main.html">Home</a></li>
							<li>Contact</li>
						</ul>
						<h1 class="white-text">Get In Touch</h1>

					</div>
				</div>
			</div>

		</div>
		<!-- /Hero-area -->

		<!-- Contact -->
		<div id="contact" class="section">

			<!-- container -->
			<div class="container">

				<!-- row -->
				<div class="row">

					<!-- contact form -->
					<div class="col-md-6" >
						<div class="contact-form glass-card" style="padding: 30px; margin-bottom: 20px;">
							<h4 style="color: var(--accent) !important;">Send A Message</h4>
							<form>
								<input class="input" type="text" name="name" placeholder="Name" style="margin-bottom: 15px;">
								<input class="input" type="email" name="email" placeholder="Email" style="margin-bottom: 15px;">
								<input class="input" type="text" name="subject" placeholder="Subject" style="margin-bottom: 15px;">
								<textarea class="input" name="message" placeholder="Enter your Message" style="margin-bottom: 15px; height: 120px;"></textarea>
								<button class="main-button icon-button pull-right">Send Message</button>
							</form>
						</div>
					</div>
					<!-- /contact form -->

					<!-- contact information -->
					<div class="col-md-5 col-md-offset-1">
						<div class="glass-card" style="padding: 30px;">
							<h4 style="color: var(--accent) !important;">Contact Information</h4>
							<ul class="contact-details">
								<li><i class="fa fa-envelope" style="color: var(--accent);"></i> Careerly@gmail.com</li>
								<li><i class="fa fa-phone" style="color: var(--accent);"></i> 122-547-223-45</li>
								<li><i class="fa fa-map-marker" style="color: var(--accent);"></i> Santacruz west</li>
							</ul>

							<!-- contact map -->
							<div id="contact-map" style="height: 250px; border-radius: 12px; margin-top: 20px;"></div>
							<!-- /contact map -->
						</div>
					</div>
					<!-- contact information -->

				</div>
				<!-- /row -->

			</div>
			<!-- /container -->

		</div>
		<!-- /Contact -->

		
		<!-- Footer -->
		<footer id="footer" class="section">

			<!-- container -->
			<div class="container">

				<!-- row -->
				<div class="row">

					<!-- footer logo -->
					<div class="col-md-6">
						<div class="footer-logo">
							<a class="logo" style="font-size: 30px;" href="main.html">PathIntel.ai</a>
						</div>
					</div>
					<!-- footer logo -->


				</div>
				<!-- /row -->

				<!-- row -->
				<div id="bottom-footer" class="row">

					<!-- social -->
					<div class="col-md-4 col-md-push-8">
						
					</div>
					<!-- /social -->

					<!-- copyright -->
					<div class="col-md-8 col-md-pull-4">
						<div class="footer-copyright">
							<span>&copy; Akshat Saraswat , Tanishtha Singh  </span>
						</div>
					</div>
					<!-- /copyright -->

				</div>
				<!-- row -->

			</div>
			<!-- /container -->

		</footer>
		<!-- /Footer -->
		
		<!-- preloader -->
		<div id='preloader'><div class='preloader'></div></div>
		<!-- /preloader -->


		<!-- jQuery Plugins -->
		<script type="text/javascript" src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/bootstrap.min.js"></script>
		<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
		<script type="text/javascript" src="js/google-map.js"></script>
		<script type="text/javascript" src="js/main.js"></script>

	</body>
</html>
