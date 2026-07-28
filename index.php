<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
$bookings = loadBookings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nextern Consultancy - ROI-Focused Digital Marketing Agency India</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
</head>
<body>
<nav id="navbar">
  <a href="#" class="nav-logo">NEXTERN<span>.</span></a>
  <ul class="nav-links">
    <li><a href="#about">About</a></li>
    <li><a href="#services">Services</a></li>
    <li><a href="#book-meeting">Book Meeting</a></li>
    <li><a href="#why">Why Us</a></li>
    <li><a href="#process">Process</a></li>
    <li><a href="#testimonials">Results</a></li>
    <li><a href="#faq">FAQ</a></li>
  </ul>
  <a href="#cta" class="nav-cta">Free Growth Analysis</a>
  <button class="hamburger" id="hamburger" aria-label="Open menu">
    <span></span><span></span><span></span>
  </button>
</nav>
<div class="mobile-menu" id="mobileMenu">
  <button class="mobile-close" id="mobileClose">X</button>
  <a href="#about" class="mobile-link">About</a>
  <a href="#services" class="mobile-link">Services</a>
  <a href="#book-meeting" class="mobile-link">Book Meeting</a>
  <a href="#why" class="mobile-link">Why Us</a>
  <a href="#process" class="mobile-link">Process</a>
  <a href="#testimonials" class="mobile-link">Results</a>
  <a href="#cta" class="mobile-link" style="color:var(--accent)">Get Free Analysis</a>
</div>
<section id="hero">
  <div class="hero-left">
    <div class="hero-eyebrow">ROI-Focused Marketing Agency · India</div>
    <h1 class="hero-h1">We Do Not Drive Traffic.<br>We Deliver <em>Customers.</em></h1>
    <p class="hero-sub">Give your brand a boost with <strong>Nextern Consultancy</strong> - a results-driven digital marketing agency built for Indian businesses ready to scale.</p>
    <a href="#cta" class="btn-primary">Get Your Free Growth Analysis</a>
  </div>
  <div class="hero-right">
    <div class="hero-card">
      <div class="stat-grid">
        <div class="stat"><div class="stat-num">3x</div><div class="stat-label">Average ROI delivered</div></div>
        <div class="stat"><div class="stat-num">50+</div><div class="stat-label">Brands grown</div></div>
        <div class="stat-divider"></div>
        <div class="stat"><div class="stat-num">INR 2Cr</div><div class="stat-label">Ad spend managed</div></div>
        <div class="stat"><div class="stat-num">98%</div><div class="stat-label">Client retention rate</div></div>
      </div>
    </div>
  </div>
</section>
<div id="clients-ticker"><div class="ticker-track"><span class="ticker-item">VeloTech</span><span class="ticker-dot">*</span>...</div></div>
<section id="about">
  <div class="about-grid reveal">
    <div>
      <div class="section-label">Who We Are</div>
      <h2 class="section-title">Not your average<br><em>marketing agency</em></h2>
      <p class="about-body">At Nextern, we believe marketing should pay for itself - and then some. We are a team of strategists, designers, media buyers, and growth experts obsessed with one thing: measurable results.</p>
      <p class="about-body">Founded on a simple belief that every Indian business deserves marketing that actually works, we help brands turn ad spend into real revenue with qualified leads, loyal customers, and sustainable growth.</p>
      <div class="pillar-list">
        <div class="pillar"><div class="pillar-icon">01</div><div><div class="pillar-title">ROI-First Mindset</div><div class="pillar-text">Every decision we make is tied to your bottom line.</div></div></div>
        <div class="pillar"><div class="pillar-icon">02</div><div><div class="pillar-title">Built for the Indian Market</div><div class="pillar-text">We understand local platforms, audiences, and consumer behavior deeply.</div></div></div>
        <div class="pillar"><div class="pillar-icon">03</div><div><div class="pillar-title">End-to-End Execution</div><div class="pillar-text">One dedicated team managing all your channels so nothing slips through the cracks.</div></div></div>
      </div>
    </div>
    <div class="about-visual reveal" style="transition-delay:.15s">
      <div class="about-big-num">50+</div>
      <div class="about-card">
        <div class="about-card-label">The Nextern Difference</div>
        <div class="about-card-quote">When you work with us, you are not just hiring an agency - you are gaining a <em>growth partner</em>.</div>
        <div class="about-stats-row">
          <div><div class="astat-num">INR 2Cr+</div><div class="astat-lbl">Ad Spend Managed</div></div>
          <div><div class="astat-num">3X</div><div class="astat-lbl">Average ROI</div></div>
          <div><div class="astat-num">50+</div><div class="astat-lbl">Brands Grown</div></div>
        </div>
      </div>
    </div>
  </div>
</section>
<section id="services">
  <div class="section-label">What We Do</div>
  <h2 class="section-title">Services built for<br><em>real business growth</em></h2>
  <p class="services-intro">Every service we offer is engineered around one goal - returning more than you invest. No vanity metrics, no fluff.</p>
  <div class="services-grid reveal">
    <div class="service-card"><div class="service-num">01</div><div class="service-icon">FB</div><div class="service-name">Facebook and Instagram Ads</div><p class="service-desc">High-converting campaigns built for leads, sales, and awareness with research, creatives, A/B testing, and ongoing optimization.</p></div>
    <div class="service-card"><div class="service-num">02</div><div class="service-icon">GO</div><div class="service-name">Google Ads</div><p class="service-desc">Search, display, shopping, and video campaigns that place your business in front of high-intent customers at the right moment.</p></div>
    <div class="service-card"><div class="service-num">03</div><div class="service-icon">WA</div><div class="service-name">WhatsApp Marketing</div><p class="service-desc">Personalized, permission-based messaging flows that improve follow-up quality and increase conversion rates.</p></div>
    <div class="service-card"><div class="service-num">04</div><div class="service-icon">WD</div><div class="service-name">Website Development</div><p class="service-desc">Fast, mobile-first websites and landing pages that convert visitors into qualified leads and customers.</p></div>
    <div class="service-card"><div class="service-num">05</div><div class="service-icon">SM</div><div class="service-name">Social Media Management</div><p class="service-desc">Content planning, publishing, community management, and reporting to keep your social presence active and growing.</p></div>
    <div class="service-card"><div class="service-num">06</div><div class="service-icon">BR</div><div class="service-name">Branding</div><p class="service-desc">Brand strategy and visual identity systems designed to build trust and make your business more memorable.</p></div>
  </div>
</section>
<section id="book-meeting">
  <div class="meeting-grid">
    <div class="reveal">
      <div class="section-label">Schedule Meeting</div>
      <h2 class="section-title">Book your <em>calendar slot</em></h2>
      <p class="meeting-text">Choose a preferred date and share your details. Our team will confirm your meeting and next steps.</p>
      <ul class="meeting-points">
        <li>30-minute strategy consultation</li>
        <li>Calendar slot locking with conflict checks</li>
        <li>Owner-only protected records</li>
        <li>CSV export for spreadsheet use</li>
      </ul>
    </div>
    <div class="meeting-card reveal" style="transition-delay:.1s">
      <form id="meetingForm" class="meeting-form">
        <div class="meeting-field"><label for="fullName">Full Name</label><input type="text" id="fullName" name="fullName" required></div>
        <div class="meeting-field"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
        <div class="meeting-field"><label for="phone">Phone</label><input type="tel" id="phone" name="phone" placeholder="+91"></div>
        <div class="meeting-field"><label for="businessName">Business Name</label><input type="text" id="businessName" name="businessName"></div>
        <div class="meeting-field"><label for="meetingType">Meeting Type</label><select id="meetingType" name="meetingType"><option>Strategy Call</option><option>Ads Audit</option><option>Website Growth Plan</option><option>Social Media Plan</option></select></div>
        <div class="meeting-field"><label for="meetingDate">Choose Date</label><input type="date" id="meetingDate" name="meetingDate" required></div>
        <div class="meeting-field meeting-field-full"><label>Choose Time Slot</label><div id="slotGrid" class="slot-grid" role="listbox" aria-label="Available time slots"></div><p id="slotHelp" class="slot-help">Select a date to load available slots.</p><input type="hidden" id="meetingAt" name="meetingAt" required></div>
        <div class="meeting-field meeting-field-full"><label for="notes">Notes</label><textarea id="notes" name="notes" rows="4" placeholder="Tell us your growth goals"></textarea></div>
        <button type="submit" class="btn-primary">Schedule Meeting</button>
      </form>
      <p id="meetingStatus" class="meeting-status"></p>
    </div>
  </div>
</section>
<section id="why">
  <div class="why-grid">
    <div class="reveal"><div class="section-label">Why Nextern</div><h2 class="section-title">Why businesses<br><em>trust us</em></h2><p style="font-size:.95rem;color:#777;line-height:1.7;margin-bottom:.5rem;">We do not just run campaigns. We engineer growth systems that keep delivering.</p><div class="why-features"><div class="why-feat"><div class="why-feat-num">01</div><div><div class="why-feat-title">ROI-First Mindset</div><div class="why-feat-text">Every decision is tied to revenue outcomes that matter to your business.</div></div></div><div class="why-feat"><div class="why-feat-num">02</div><div><div class="why-feat-title">Transparent Reporting</div><div class="why-feat-text">Clear reports that show where your budget goes and what it returns.</div></div></div><div class="why-feat"><div class="why-feat-num">03</div><div><div class="why-feat-title">Tailored Strategies</div><div class="why-feat-text">No copy-paste playbooks. Every campaign is built for your goals.</div></div></div><div class="why-feat"><div class="why-feat-num">04</div><div><div class="why-feat-title">Local Market Expertise</div><div class="why-feat-text">We understand Indian customer behavior and channel nuances.</div></div></div><div class="why-feat"><div class="why-feat-num">05</div><div><div class="why-feat-title">End-to-End Execution</div><div class="why-feat-text">One team handling ads, social, website, and branding in one system.</div></div></div></div></div>
    <div class="reveal" style="transition-delay:.15s"><div class="why-panel"><div class="why-panel-label">Our Expertise</div><div class="why-panel-title">A full-stack growth<br><em>engine for your brand</em></div><div class="why-badge-row"><span class="why-badge">Meta Ads</span><span class="why-badge">Google Ads</span><span class="why-badge">WhatsApp</span><span class="why-badge">SEO</span><span class="why-badge">Branding</span><span class="why-badge">Web Dev</span></div><div class="why-stats"><div class="wstat"><div class="wstat-val">50+</div><div class="wstat-lbl">Brands Grown</div></div><div class="wstat"><div class="wstat-val">3X</div><div class="wstat-lbl">Average ROI</div></div><div class="wstat"><div class="wstat-val">INR 2Cr</div><div class="wstat-lbl">Ad Spend</div></div><div class="wstat"><div class="wstat-val">98%</div><div class="wstat-lbl">Retention</div></div></div></div></div>
  </div>
</section>
<section id="process"><div class="process-header reveal"><div class="section-label">How It Works</div><h2 class="section-title">Simple, focused,<br><em>effective</em></h2><p style="font-size:.95rem;color:#777;line-height:1.7;margin-top:.5rem;">From first conversation to scaling campaigns, here is exactly how we work.</p></div><div class="process-steps reveal" style="transition-delay:.1s"><div class="step-card"><div class="step-n">01</div><div class="step-title">Free Growth Analysis</div><div class="step-text">We audit your current marketing, identify gaps, and map opportunities.</div></div><div class="step-card"><div class="step-n">02</div><div class="step-title">Custom Strategy</div><div class="step-text">A practical plan with clear actions, budget guidance, and goals.</div></div><div class="step-card"><div class="step-n">03</div><div class="step-title">Launch and Execute</div><div class="step-text">Campaigns and creatives go live with disciplined execution.</div></div><div class="step-card"><div class="step-n">04</div><div class="step-title">Optimize and Scale</div><div class="step-text">Continuous testing and optimization to scale what works.</div></div></div></section>
<section id="testimonials"><div class="section-label">Client Results</div><h2 class="section-title">Real results,<br><em>real words</em></h2><p class="testi-intro">Business owners across India share what changed after partnering with Nextern.</p><div class="testimonials-grid reveal"><div class="testi-card featured"><div class="stars">*****</div><p class="testi-text">Before Nextern, we were burning money on ads with little to show for it. Within 60 days, lead volume tripled and cost per lead dropped sharply.</p><div class="testi-author"><div class="author-avatar">RM</div><div><div class="author-name">Rajesh M.</div><div class="author-role">Real Estate Developer, Pune</div></div></div></div><div class="testi-card"><div class="stars">*****</div><p class="testi-text">They redesigned our website and launched Google Ads from scratch. Enquiries moved from near zero to consistent monthly demand.</p><div class="testi-author"><div class="author-avatar">PS</div><div><div class="author-name">Priya S.</div><div class="author-role">Owner, Skincare Brand, Mumbai</div></div></div></div><div class="testi-card"><div class="testi-card"><div class="stars">*****</div><p class="testi-text">Nextern handles our social media and Meta ads. Growth is measurable, and the campaigns convert into real customers.</p><div class="testi-author"><div class="author-avatar">AT</div><div><div class="author-name">Arjun T.</div><div class="author-role">Co-Founder, D2C Fitness Brand, Bangalore</div></div></div></div></div><div class="testi-card"><div class="stars">*****</div><p class="testi-text">Their WhatsApp marketing setup improved our lead follow-up and conversion quality significantly.</p><div class="testi-author"><div class="author-avatar">SR</div><div><div class="author-name">Sneha R.</div><div class="author-role">Clinic Owner, Delhi</div></div></div></div></div></section>
<section id="faq"><div class="section-label">FAQ</div><h2 class="section-title">Frequently asked<br><em>questions</em></h2><div class="faq-grid"><div class="faq-list reveal"><div class="faq-item"><button class="faq-q" data-faq="0">Do you work with small businesses? <span class="faq-icon">+</span></button><div class="faq-a" id="faq-0"><p>Yes. We tailor service scope and budget based on your growth stage and goals.</p></div></div><div class="faq-item"><button class="faq-q" data-faq="1">How soon will I see results? <span class="faq-icon">+</span></button><div class="faq-a" id="faq-1"><p>Paid campaigns can show early results in 2-4 weeks. Organic channels take longer.</p></div></div><div class="faq-item"><button class="faq-q" data-faq="2">What is the minimum budget to get started? <span class="faq-icon">+</span></button><div class="faq-a" id="faq-2"><p>We work with multiple budget levels and recommend a realistic starting range after analysis.</p></div></div><div class="faq-item"><button class="faq-q" data-faq="3">Will I get regular updates on performance? <span class="faq-icon">+</span></button><div class="faq-a" id="faq-3"><p>Yes. You receive clear reporting and regular strategy updates.</p></div></div><div class="faq-item"><button class="faq-q" data-faq="4">Do you offer long-term contracts? <span class="faq-icon">+</span></button><div class="faq-a" id="faq-4"><p>Flexible models are available. We recommend at least 3 months for measurable outcomes.</p></div></div><div class="faq-item"><button class="faq-q" data-faq="5">Can you handle multiple services together? <span class="faq-icon">+</span></button><div class="faq-a" id="faq-5"><p>Yes. Integrated execution is where we typically create the strongest growth outcomes.</p></div></div></div><div class="faq-panel reveal" style="transition-delay:.15s"><div class="section-label">Still have questions?</div><div class="faq-panel-title">Let us talk about<br><em>your business</em></div><p class="faq-panel-text">Book a free 30-minute growth analysis call. No pressure, just practical insights for your next growth phase.</p><a href="mailto:hello@nexternconsultancy.com" class="btn-primary">Book Your Free Call</a><div class="contact-list"><div class="contact-row"><div class="contact-icon">@</div> hello@nexternconsultancy.com</div><div class="contact-row"><div class="contact-icon">T</div> +91 XXXXX XXXXX</div><div class="contact-row"><div class="contact-icon">L</div> India</div></div></div></div></section>
<div id="cta"><div><div class="cta-text">Ready to turn your marketing<br>into a <em>revenue engine?</em></div><p class="cta-sub">Stop guessing and start growing. Free analysis and zero obligations.</p><div class="cta-note">Limited monthly slots available.</div></div><a href="mailto:hello@nexternconsultancy.com" class="btn-white">Get Your Free Growth Analysis</a></div>
<footer>... </footer>
<script src="script.js"></script>
</body>
</html>
