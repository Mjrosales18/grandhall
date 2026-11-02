<?php 
include('db.php');
include('header.php');
?>

<div class="card">
    <h2>🏛️ About Grand M.J Rosales Horizon Function Hall</h2>
    <p style="font-size: 1.05rem; color: var(--color-text-main); margin-bottom: 2rem;">
        Welcome to <strong>Grand M.J Rosales Horizon Function Hall</strong>, your premier destination for memorable celebrations, grand gatherings, and professional events.
    </p>

    <!-- Visual Showcase Image Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 2rem;">
        
        <div style="background: #ffffff; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--color-ice-light); box-shadow: var(--shadow-sm);">
            <img src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=600&q=80" alt="Main Hall Setup" style="width: 100%; height: 180px; object-fit: cover;">
            <div style="padding: 1rem;">
                <h4 style="color: #1F232B; margin-bottom: 0.25rem;">Elegant Event Space</h4>
                <p style="font-size: 0.88rem; color: var(--color-text-muted); margin: 0;">Spacious air-conditioned layout accommodating up to 400 guests comfortably.</p>
            </div>
        </div>

        <div style="background: #ffffff; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--color-ice-light); box-shadow: var(--shadow-sm);">
            <img src="https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=600&q=80" alt="Banquet & Dining Setup" style="width: 100%; height: 180px; object-fit: cover;">
            <div style="padding: 1rem;">
                <h4 style="color: #1F232B; margin-bottom: 0.25rem;">Custom Banquet Arrangements</h4>
                <p style="font-size: 0.88rem; color: var(--color-text-muted); margin: 0;">Tailored seating layouts perfect for weddings, debuts, and corporate galas.</p>
            </div>
        </div>

        <!-- Stage & Mood Lighting Card -->
<div style="background: #ffffff; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--color-ice-light); box-shadow: var(--shadow-sm);">
    <img src="https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&w=800&q=80" alt="Stage & Mood Lighting" style="width: 100%; height: 200px; object-fit: cover;">
    <div style="padding: 1rem;">
        <h4 style="color: #1F232B; margin-bottom: 0.25rem;">Stage & Mood Lighting</h4>
        <p style="font-size: 0.88rem; color: var(--color-text-muted); margin: 0;">Integrated high-grade sound systems, microphones, and professional stage lights.</p>
    </div>
</div>

    </div>

    <!-- Mission & Highlights Section -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <div style="background: var(--color-bg-page); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid #D4AF37;">
            <h3>✨ Our Mission</h3>
            <p style="font-size: 0.95rem; color: var(--color-text-muted); margin: 0;">
                To provide a sophisticated, fully equipped venue paired with exceptional service, ensuring every event—from intimate birthdays to majestic weddings—is executed flawlessly.
            </p>
        </div>

        <div style="background: var(--color-bg-page); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid #1F232B;">
            <h3>🌟 Venue Highlights</h3>
            <ul style="padding-left: 20px; font-size: 0.95rem; color: var(--color-text-muted); margin: 0;">
                <li>Spacious air-conditioned hall with elegant lighting</li>
                <li>Flexible seating layouts for up to 400 guests</li>
                <li>Integrated audio/visual system for presentations & music</li>
                <li>Ample parking space for 200+ vehicles</li>
            </ul>
        </div>
    </div>

    <div style="margin-top: 2.5rem; text-align: center;">
        <a href="calendar.php" class="btn btn-success" style="font-size: 1.05rem; padding: 12px 32px;">📅 Reserve Your Date Now</a>
    </div>
</div>

<!-- Lower Part: Contact Us Section -->
<div class="card" id="contact-us" style="background: #ffffff; border-top: 5px solid #D4AF37;">
    <h2>📞 Contact Us</h2>
    <p style="color: var(--color-text-muted); margin-bottom: 1.5rem;">Get in touch with us for inquiries, site visits, and booking reservations.</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        
        <div style="background: var(--color-bg-page); padding: 1.25rem; border-radius: var(--radius-md); display: flex; align-items: center; gap: 15px; border-left: 4px solid #D4AF37;">
            <img src="images/Phone.png" alt="Phone Icon" style="width: 38px; height: 38px; object-fit: contain;">
            <div>
                <strong>Phone Number</strong>
                <p style="margin: 0; color: var(--color-text-muted); font-weight: 600;">09526149170</p>
            </div>
        </div>

        <div style="background: var(--color-bg-page); padding: 1.25rem; border-radius: var(--radius-md); display: flex; align-items: center; gap: 15px; border-left: 4px solid #1F232B;">
            <img src="images/Email.png" alt="Email Icon" style="width: 38px; height: 38px; object-fit: contain;">
            <div>
                <strong>Email Address</strong>
                <p style="margin: 0;">
                    <a href="mailto:mjrosales200618@gmail.com" style="color: #D4AF37; font-weight: 600; text-decoration: none;">mjrosales200618@gmail.com</a>
                </p>
            </div>
        </div>

        <div style="background: var(--color-bg-page); padding: 1.25rem; border-radius: var(--radius-md); display: flex; align-items: center; gap: 15px; border-left: 4px solid #2E3542;">
            <img src="images/facebook.png" alt="Facebook Icon" style="width: 38px; height: 38px; object-fit: contain;">
            <div>
                <strong>Facebook Page</strong>
                <p style="margin: 0;">
                    <a href="https://facebook.com" target="_blank" style="color: #D4AF37; font-weight: 600; text-decoration: none;">Grand M.J Rosales Horizon Function Hall</a>
                </p>
            </div>
        </div>

    </div>
</div>

<?php include('footer.php'); ?>