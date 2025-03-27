/* =========================================================================
   Gatita Bakes Ordering - Custom Stylesheet v1.4.0 (Dynamic Form Update)
   ========================================================================= */

/* --- Basic Reset & Defaults --- */
#gatita-order-form *, .gatita-hero-section * { box-sizing: border-box; }

/* --- Hero Section Styles (Unchanged) --- */
.gatita-hero-section { width: 100vw; position: relative; left: 50%; transform: translateX(-50%); overflow: hidden; box-sizing: border-box; background-image: url('../images/hero-page-fullpage.png'); background-size: cover; background-position: center center; background-repeat: no-repeat; min-height: 60vh; display: flex; justify-content: center; align-items: center; padding: 40px 20px; text-align: center; margin-bottom: 30px; }
.gatita-hero-content { background-color: rgba(40, 30, 25, 0.4); padding: 30px 40px; border-radius: 8px; max-width: 600px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
.gatita-hero-content h1 { color: #fff; font-size: 3.2em; margin-top: 0; margin-bottom: 0.5em; text-shadow: 2px 2px 4px rgba(0,0,0,0.6); }
.gatita-hero-content p { color: #eee; font-size: 1.3em; margin-bottom: 1.5em; text-shadow: 1px 1px 3px rgba(0,0,0,0.5); }

/* --- General Button Styles (Unchanged) --- */
.gatita-button { display: inline-block; background-color: #e5a98c; color: #fff; padding: 12px 25px; border: none; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 1em; cursor: pointer; transition: background-color 0.3s ease, transform 0.2s ease; text-transform: uppercase; letter-spacing: 1px; }
.gatita-button:hover, .gatita-button:focus { background-color: #d4987a; color: #fff; transform: translateY(-2px); outline: none; }
.gatita-place-order-button { padding: 15px 35px; width: 100%; }

/* --- Order Form - General & Two-Column Layout (Unchanged) --- */
#gatita-order-form { max-width: 1100px; margin: 20px auto; padding: 10px; }
#gatita-order-form h2 { text-align: center; color: #5a4e46; margin-top: 1.5em; margin-bottom: 1em; font-weight: 600; font-size: 1.8em; }
.gatita-order-page-wrapper { display: flex; flex-direction: column; gap: 30px; }
@media (min-width: 820px) { .gatita-order-page-wrapper { flex-direction: row; gap: 40px; align-items: flex-start; } .gatita-order-main-content { flex: 2; } .gatita-order-sidebar { flex: 1; position: sticky; top: 40px; } }
.gatita-order-sidebar h2 { text-align: left; font-size: 1.5em; margin-top: 1em; margin-bottom: 0.8em; border-bottom: 1px solid #eee; padding-bottom: 0.4em; }
.gatita-order-sidebar .gatita-sidebar-section:first-child h2 { margin-top: 0; }
.gatita-sidebar-section { margin-bottom: 25px; }

/* --- Order Form - Product Slider (Unchanged from v1.3.0) --- */
.gatita-order-main-content h2 { margin-bottom: 1.5em; }
.gatita-product-slider { margin-left: auto; margin-right: auto; position: relative; overflow: hidden; list-style: none; padding: 0; z-index: 1; width: 100%; padding-bottom: 30px; }
.swiper-wrapper { position: relative; width: 100%; height: 100%; z-index: 1; display: flex; transition-property: transform; box-sizing: content-box; }
.swiper-slide { flex-shrink: 0; width: 100%; height: 100%; position: relative; transition-property: transform; padding: 5px; }
.swiper-slide .gatita-product-card { border: 1px solid #e0dcd8; border-radius: 8px; padding: 15px; text-align: center; background-color: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); height: 100%; display: flex; flex-direction: column; }
.swiper-slide .gatita-product-card img { max-width: 100%; height: auto; aspect-ratio: 4 / 3; object-fit: cover; border-radius: 4px; margin-bottom: 15px; }
.swiper-slide .gatita-product-card h3 { color: #5a4e46; margin-top: 0; margin-bottom: 0.5em; font-size: 1.2em; }
.swiper-slide .gatita-product-card p { color: #666; line-height: 1.5; font-size: 0.9em; margin-bottom: 1em; flex-grow: 1; }
.swiper-slide .gatita-product-card .price { font-weight: bold; color: #8b7a70; font-size: 1.1em; margin-top: auto; margin-bottom: 15px; flex-grow: 0; }
.swiper-slide .gatita-product-order-controls { margin-top: 10px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; flex-grow: 0; }
.swiper-slide .gatita-product-order-controls label { cursor: pointer; font-size: 0.9em; }
.swiper-slide .gatita-product-order-controls input[type="checkbox"] { margin-right: 5px; width: 16px; height: 16px; }
.swiper-slide .gatita-product-order-controls .quantity-label { margin-left: auto; margin-right: 5px; }
.swiper-slide .gatita-product-order-controls .quantity-input { width: 55px; padding: 5px 8px; border: 1px solid #ccc; border-radius: 4px; text-align: center; font-size: 0.9em; }
.swiper-slide .gatita-product-order-controls input[type=number]::-webkit-inner-spin-button, .swiper-slide .gatita-product-order-controls input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; } .swiper-slide .gatita-product-order-controls input[type=number] { -moz-appearance: textfield; }
/* Swiper Navigation/Pagination Styles (Unchanged) */
.swiper-button-prev, .swiper-button-next { position: absolute; top: 50%; width: calc(var(--swiper-navigation-size)/ 44 * 27); height: var(--swiper-navigation-size); margin-top: calc(0px - (var(--swiper-navigation-size)/ 2)); z-index: 10; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--swiper-navigation-color, #e5a98c); background-color: rgba(255, 255, 255, 0.7); border-radius: 50%; padding: 5px; transition: background-color 0.3s ease; }
.swiper-button-prev:hover, .swiper-button-next:hover { background-color: rgba(255, 255, 255, 0.9); }
.swiper-button-prev { left: 10px; right: auto; } .swiper-button-next { right: 10px; left: auto; }
.swiper-button-disabled { opacity: 0.35; cursor: auto; pointer-events: none; }
.swiper-pagination { position: absolute; text-align: center; transition: 300ms opacity; transform: translate3d(0, 0, 0); z-index: 10; } .swiper-pagination.swiper-pagination-hidden { opacity: 0; }
.swiper-horizontal>.swiper-pagination-bullets, .swiper-pagination-bullets.swiper-pagination-horizontal { bottom: 10px; left: 0; width: 100%; }
.swiper-pagination-bullet { width: var(--swiper-pagination-bullet-width, var(--swiper-pagination-bullet-size, 8px)); height: var(--swiper-pagination-bullet-height, var(--swiper-pagination-bullet-size, 8px)); display: inline-block; border-radius: 50%; background: var(--swiper-pagination-bullet-inactive-color, #ccc); opacity: var(--swiper-pagination-bullet-inactive-opacity, 0.4); }
.swiper-pagination-bullet-active { opacity: 1; background: var(--swiper-pagination-color, #e5a98c); }

/* --- Order Form - Sidebar Fields (Mostly Unchanged) --- */
.gatita-order-sidebar .gatita-form-row { margin-bottom: 18px; }
.gatita-order-sidebar label { display: block; margin-bottom: 6px; font-weight: bold; color: #5a4e46; font-size: 0.9em; }
.gatita-order-sidebar input[type="text"], .gatita-order-sidebar input[type="email"], .gatita-order-sidebar input[type="tel"], .gatita-order-sidebar textarea,
.gatita-order-sidebar select { /* Added select */
    width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 0.95em; transition: border-color 0.3s ease, box-shadow 0.3s ease;
    background-color: #fff; /* Ensure select bg is white */
}
.gatita-order-sidebar input:focus, .gatita-order-sidebar textarea:focus,
.gatita-order-sidebar select:focus { /* Added select */
    border-color: #e5a98c; outline: none; box-shadow: 0 0 0 2px rgba(229, 169, 140, 0.2);
}
.gatita-order-sidebar textarea { min-height: 80px; resize: vertical; }
.required { color: #d9534f; margin-left: 3px; }
.gatita-radio-group label { display: block; margin-bottom: 8px; font-weight: normal; cursor: pointer; font-size: 1em; }
.gatita-radio-group input[type="radio"] { margin-right: 8px; vertical-align: middle; }

/* ==========================================
   NEW: Conditional Pickup/Delivery Section Styles
   ========================================== */
.gatita-form-section-dynamic {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px dashed #ccc;
    /* --- Initial hidden state & transition --- */
    overflow: hidden; /* Important for max-height transition */
    max-height: 0;
    opacity: 0;
    transform: translateY(-10px); /* Optional: Slight move up effect */
    transition: max-height 0.5s ease-out, opacity 0.4s ease-in, transform 0.4s ease-out;
    visibility: hidden; /* Ensure it's not accessible when hidden */
}
.gatita-form-section-dynamic.visible {
    max-height: 1000px; /* Needs to be large enough for content */
    opacity: 1;
    transform: translateY(0);
    visibility: visible;
    transition: max-height 0.6s ease-in, opacity 0.5s ease-in, transform 0.5s ease-in;
}

/* Add some specific styles if needed for the headings inside dynamic sections */
.gatita-form-section-dynamic h3 {
    font-size: 1.1em;
    color: #666;
    margin-top: 0;
    margin-bottom: 5px;
    text-align: left;
    border-bottom: none; /* Remove border from main h2 */
}
.gatita-form-section-dynamic p small { font-size: 0.9em; color: #777; display: block; margin-bottom: 15px; }


/* --- Submit Button Area (Unchanged) --- */
.gatita-submit-row { text-align: center; margin-top: 10px; }
.gatita-payment-note { text-align: center; font-size: 0.85em; color: #777; margin-top: 15px; }

/* --- Notification/Status Messages (Unchanged) --- */
.gatita-notice { padding: 15px 20px; margin: 20px auto; border-radius: 5px; border: 1px solid transparent; max-width: 1100px; }
.gatita-notice-success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
.gatita-notice-error { color: #a94442; background-color: #f2dede; border-color: #ebccd1; }

/* --- Responsive Adjustments (Unchanged) --- */
@media (max-width: 819px) { #gatita-order-form { max-width: 95%; } }
@media (max-width: 480px) { /* Styles for small mobile ... */ }
