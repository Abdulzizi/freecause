<style>
.global-footer {
    background: #1f2024;
    color: #fff;
    font-family: Arial, sans-serif;
}

.global-footer .container {
   max-width: 1300px;
    max-height: 800px; 
    margin-right: auto;
    margin-left: auto;
}
.footer-inner { 
    padding: 40px; 
}
.footer-logo {
    text-align: center;
}
.footer-about img {
    max-width: 306px;
    margin-bottom: 20px;
}

.footer-about p {
    font-size: 15px;
    margin-bottom: 0px;
    color: #f2f2f2;
}

.footer-about a {
    color: #fff;
    text-decoration: underline;
}

.footer-links {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.footer-links a {
    color: #fff;
    text-decoration: none;
    font-size: 14px;
}

.footer-links a:hover {
    text-decoration: underline;
}

.footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.2);
    text-align: end;
    padding: 20px;
    font-size: 15px; 
	color: #f2f2f2;
}
.footer-links {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    justify-content: flex-start;
    column-gap: 20px;
    text-align: left;
    padding-top: 20px;
    padding-bottom: 30px;
	margin-top: 30px;
}
/* 🔻 Mobile */
@media (max-width: 768px) {
    .footer-inner {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .footer-links {
        align-items: center;
    }
}

</style>
<section class="global-footer">
<div class="container">
	<div class="row">
		<div class="col-12">
			
		
<div id="wp-global-footer">
    <div class="footer-inner">

        <!-- Logo + Description -->
        <div class="footer-about">
			<div class="footer-logo">
				
            <img src="https://www.freecause.com/magazine/wp-content/uploads/2025/01/19.png" alt="Freecause">

			</div>
            <p>
               Freecause Magazine is your go-to resource for stories that inspire action and empower change. We bring you the latest insights on advocacy, community movements, and global causes, along with expert advice on how to make a difference through petitions and grassroots campaigns. From impactful success stories to practical guides, we aim to educate, inform, and motivate you to stand up for what matters.</p>
<p>Our content spans a variety of topics, including social justice, environmental advocacy, civil rights, and emerging technologies shaping modern activism. Whether you’re starting your first petition or looking to support meaningful initiatives, Freecause Magazine is here to guide and inspire your journey toward making an impact.
            </p>
<br>
            <p>
               For inquiries, feedback, or to report any inaccuracies, please reach out to our editorial team at <a href="mailto:hello@freecause.com">hello@freecause.com</a>. We are committed to maintaining the accuracy and integrity of our content and welcome your input to ensure we deliver trustworthy and reliable information.
            </p>
        </div>

        <!-- Footer Links -->
        <div class="footer-links">
            <a href="/about">About Us</a>
            <a href="/privacy-policy">Privacy Policy</a>
            <a href="/ethical-code">Ethical Code</a>
            <a href="/contacts">Contact</a>
            <a href="/terms-of-service">Terms of Service</a>
        </div>

    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom">
       © <?php echo date('Y'); ?> Freecause – Freedom in Sharing™ - Freecause LLC, Albuquerque, NM, USA - All rights reserved
    </div>
</div>
</div>
	</div>
	</div>
</section>