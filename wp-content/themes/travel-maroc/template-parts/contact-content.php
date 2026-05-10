<?php $contact_bg_url = get_option('tma_contact_bg_url'); ?>
<div class="section"<?php if ( $contact_bg_url ) : ?> style="background:linear-gradient(rgba(255,255,255,.92),rgba(255,255,255,.92)),url(<?php echo esc_url($contact_bg_url); ?>) center/cover fixed"<?php endif; ?>>
    <div class="container">
        <div class="text-center" style="margin-bottom:3rem">
            <h1 class="section-title">Contactez-nous</h1>
            <p class="section-sub">Notre équipe est là pour vous aider à planifier votre voyage idéal</p>
        </div>
        <div class="contact-grid">
            <div>
                <form class="contact-form" id="tma-contact-form">
                    <div class="form-group">
                        <label>Nom complet *</label>
                        <input type="text" name="name" required placeholder="Votre nom">
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required placeholder="votre@email.com">
                    </div>
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="tel" name="phone" placeholder="+212 6XX-XX-XX-XX">
                    </div>
                    <div class="form-group">
                        <label>Destination souhaitée</label>
                        <select name="destination">
                            <option value="">-- Choisir --</option>
                            <option>Marrakech</option><option>Désert Merzouga</option>
                            <option>Chefchaouen</option><option>Istanbul</option>
                            <option>Dubaï</option><option>Paris</option>
                            <option>Excursion journée</option><option>Autre</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Message *</label>
                        <textarea name="message" rows="5" required placeholder="Décrivez votre projet de voyage..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%">Envoyer le message</button>
                    <div id="contact-status" style="margin-top:1rem;font-size:.9rem"></div>
                </form>
            </div>
            <div>
                <h3 style="color:var(--bleu);margin-bottom:1.5rem;font-size:1.25rem">Informations de contact</h3>
                <?php
                $c_address = tma_get_option_translated('tma_contact_address', "EST Dakhla, Route de l'Aéroport\nDakhla 73000, Maroc");
                $c_phone   = get_option('tma_contact_phone',   '+212 5XX-XX-XX-XX');
                $c_email   = get_option('tma_contact_email',   'contact@travelmaroc.ma');
                $c_wa      = get_option('tma_whatsapp_number', '212500000000');
                ?>
                <div class="contact-info-item">
                    <div class="contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
                    <div><strong>Adresse</strong><br><?php echo nl2br( esc_html($c_address) ); ?></div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.09 19.79 19.79 0 010 .45 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.16 6.16l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg></div>
                    <div><strong>Téléphone</strong><br><?php echo esc_html($c_phone); ?>
                        <?php if ($c_wa) : ?><br><a href="https://wa.me/<?php echo esc_attr($c_wa); ?>" style="color:var(--bleu);font-size:.88rem">WhatsApp →</a><?php endif; ?>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
                    <div><strong>Email</strong><br><a href="mailto:<?php echo esc_attr($c_email); ?>" style="color:var(--bleu)"><?php echo esc_html($c_email); ?></a></div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                    <div><strong>Horaires</strong><br>Lundi – Samedi : 9h00 – 18h00<br>Dimanche : Fermé</div>
                </div>
                <div style="background:var(--bleu-light);border-radius:var(--radius-lg);padding:1.5rem;margin-top:1rem">
                    <strong style="color:var(--bleu)">📍 Carte interactive</strong>
                    <p style="font-size:.88rem;color:var(--gris);margin-top:.5rem">Retrouvez-nous sur Google Maps : <em>Travel Maroc Agency, Dakhla</em></p>
                </div>
            </div>
        </div>
    </div>
</div>
