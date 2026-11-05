/* ============================================================
   MultiShop — JavaScript principal
   Animations GSAP, carte Leaflet, utilitaires
   ============================================================ */

window.MultiShop = (function () {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ----------------------------- Animations GSAP ----------------------------- */

    function initAnimations() {
        if (typeof gsap === 'undefined' || reducedMotion) return;

        if (typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);
        }

        // Héro de la page d'accueil
        const heroTitle = document.querySelector('.ms-hero-title');
        if (heroTitle) {
            gsap.from('.ms-hero-title', { y: 36, opacity: 0, duration: .8, ease: 'power3.out' });
            gsap.from('.ms-hero-sub', { y: 28, opacity: 0, duration: .8, delay: .15, ease: 'power3.out' });
            gsap.from('.ms-hero-search', { y: 24, opacity: 0, duration: .8, delay: .3, ease: 'power3.out' });
            gsap.from('.ms-hero-logo', { scale: .85, opacity: 0, duration: 1, delay: .2, ease: 'back.out(1.6)' });
        }

        // Cartes : apparition en cascade au défilement
        const cards = gsap.utils.toArray('.anim-card');
        if (cards.length && typeof ScrollTrigger !== 'undefined') {
            ScrollTrigger.batch(cards, {
                start: 'top 92%',
                once: true,
                onEnter: function (batch) {
                    gsap.fromTo(batch,
                        { y: 30, opacity: 0 },
                        { y: 0, opacity: 1, duration: .55, stagger: .07, ease: 'power2.out', overwrite: true }
                    );
                }
            });
            // Sécurité : tout rendre visible après chargement complet
            window.addEventListener('load', function () { ScrollTrigger.refresh(); });
        }

        // Pastilles de catégories
        const chips = gsap.utils.toArray('.anim-chip');
        if (chips.length) {
            gsap.from(chips, { scale: .7, opacity: 0, duration: .4, stagger: .04, ease: 'back.out(2)', delay: .2 });
        }

        // Badge du panier
        const badge = document.querySelector('.ms-cart-badge');
        if (badge) {
            gsap.from(badge, { scale: 0, duration: .5, ease: 'back.out(3)', delay: .4 });
        }

        // Coche de succès de commande
        const check = document.querySelector('.ms-success-check');
        if (check) {
            gsap.from(check, { scale: 0, rotation: -30, duration: .7, ease: 'back.out(2.2)' });
        }

        // Compteurs animés (tableau de bord admin)
        document.querySelectorAll('[data-counter]').forEach(function (el) {
            const target = parseInt(el.dataset.counter, 10);
            if (isNaN(target) || target <= 0) return;
            const obj = { val: 0 };
            const finalText = el.textContent;
            const suffix = /FCFA\s*$/.test(finalText) ? ' FCFA' : '';
            gsap.to(obj, {
                val: target, duration: 1.1, ease: 'power2.out', delay: .25,
                onUpdate: function () {
                    el.textContent = Math.round(obj.val).toLocaleString('fr-FR') + suffix;
                },
                onComplete: function () { el.textContent = finalText; }
            });
        });
    }

    /* ----------------------------- Carte Leaflet (livraison) ----------------------------- */

    // Centre par défaut : Abidjan
    const DEFAULT_CENTER = [5.345, -4.024];

    function initDeliveryMap(mapId, latInputId, lngInputId, locateBtnId) {
        if (typeof L === 'undefined') return;

        const latInput = document.getElementById(latInputId);
        const lngInput = document.getElementById(lngInputId);

        const hasOld = latInput.value && lngInput.value;
        const center = hasOld ? [parseFloat(latInput.value), parseFloat(lngInput.value)] : DEFAULT_CENTER;

        const map = L.map(mapId).setView(center, hasOld ? 15 : 12);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        let marker = hasOld ? L.marker(center, { draggable: true }).addTo(map) : null;

        function setPoint(latlng) {
            latInput.value = latlng.lat.toFixed(7);
            lngInput.value = latlng.lng.toFixed(7);
            if (marker) {
                marker.setLatLng(latlng);
            } else {
                marker = L.marker(latlng, { draggable: true }).addTo(map);
                marker.on('dragend', function () { setPoint(marker.getLatLng()); });
            }
        }

        if (marker) {
            marker.on('dragend', function () { setPoint(marker.getLatLng()); });
        }

        map.on('click', function (e) { setPoint(e.latlng); });

        // Bouton "Ma position actuelle"
        const locateBtn = document.getElementById(locateBtnId);
        if (locateBtn && navigator.geolocation) {
            locateBtn.addEventListener('click', function () {
                locateBtn.disabled = true;
                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        const latlng = L.latLng(pos.coords.latitude, pos.coords.longitude);
                        setPoint(latlng);
                        map.setView(latlng, 16);
                        locateBtn.disabled = false;
                    },
                    function () { locateBtn.disabled = false; },
                    { enableHighAccuracy: true, timeout: 8000 }
                );
            });
        } else if (locateBtn) {
            locateBtn.style.display = 'none';
        }
    }

    function initViewMap(mapId, lat, lng) {
        if (typeof L === 'undefined') return;

        const map = L.map(mapId, { scrollWheelZoom: false }).setView([lat, lng], 15);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map);
    }

    /* ----------------------------- Utilitaires ----------------------------- */

    function previewImage(input, imgId) {
        const img = document.getElementById(imgId);
        if (input.files && input.files[0] && img) {
            img.src = URL.createObjectURL(input.files[0]);
        }
    }

    function initHelpers() {
        // Boutons "copier" (lien de parrainage)
        document.querySelectorAll('[data-copy]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const target = document.querySelector(btn.dataset.copy);
                if (!target) return;
                navigator.clipboard.writeText(target.value || target.textContent).then(function () {
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.className = 'bi bi-check-lg';
                        setTimeout(function () { icon.className = 'bi bi-clipboard'; }, 1500);
                    }
                });
            });
        });

        // Formatage du numéro de carte : espaces tous les 4 chiffres
        const cardNumber = document.getElementById('card-number');
        if (cardNumber) {
            cardNumber.addEventListener('input', function () {
                const digits = cardNumber.value.replace(/\D/g, '').slice(0, 19);
                cardNumber.value = digits.replace(/(.{4})/g, '$1 ').trim();
            });
        }

        // Expiration : ajoute le "/" automatiquement
        const cardExpiry = document.getElementById('card-expiry');
        if (cardExpiry) {
            cardExpiry.addEventListener('input', function () {
                let v = cardExpiry.value.replace(/[^\d]/g, '').slice(0, 4);
                if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
                cardExpiry.value = v;
            });
        }

        // Les alertes flash disparaissent après 5 secondes
        document.querySelectorAll('.ms-flash .alert').forEach(function (alert) {
            setTimeout(function () {
                if (window.bootstrap && bootstrap.Alert) {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }
            }, 5000);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initAnimations();
        initHelpers();
    });

    return {
        initDeliveryMap: initDeliveryMap,
        initViewMap: initViewMap,
        previewImage: previewImage
    };
})();
