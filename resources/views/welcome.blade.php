<!doctype html>
<html class="no-js" lang="zxx">

<head>

   <meta charset="utf-8">
   <meta http-equiv="x-ua-compatible" content="ie=edge">
   <title>Sistema de Trámites - Alcaldía de Puerto Boyacá</title>
   <meta name="description" content="Plataforma oficial de la Alcaldía de Puerto Boyacá para gestionar en línea afiliaciones ARL, actas de necesidad, contratación, plan de adquisiciones y solicitudes BPIM.">
   <meta name="viewport" content="width=device-width, initial-scale=1">

   <!-- Place favicon.ico in the root directory -->
   <link rel="shortcut icon" type="image/x-icon" href="/cunnet/assets/img/logo/favicon.png">

   <!-- CSS here -->
   <link rel="stylesheet" href="/cunnet/assets/css/bootstrap.min.css">
   <link rel="stylesheet" href="/cunnet/assets/css/swiper-bundle.css">
   <link rel="stylesheet" href="/cunnet/assets/css/nice-select.css">
   <link rel="stylesheet" href="/cunnet/assets/css/magnific-popup.css">
   <link rel="stylesheet" href="/cunnet/assets/css/font-awesome-pro.css">
   <link rel="stylesheet" href="/cunnet/assets/css/spacing.css">
   <link rel="stylesheet" href="/cunnet/assets/css/main.css">

   <style>
      /* Color institucional (azul de la Alcaldía) en acentos y botones */
      .tp-btn-red{ background-color: var(--tp-theme-primary) !important; }
      ::selection{ background: var(--tp-theme-primary); color:#fff; }
      /* Hero: reducir el título para que las palabras rotativas no ocupen todo */
      .ca-hero-title{ font-size: clamp(3rem, 6.5vw, 6.5rem) !important; line-height: 1.08 !important; }
      .ca-hero-title .cd-words-wrapper{ font-size: .82em; }
      /* Hero: imagen (reemplaza el video) se muestra completa */
      .ca-hero-video img{ display:block; width:100%; height:auto; border-radius:10px; }
   </style>
</head>

<body class="tp-magic-cursor">

   <!-- preloader -->
   <div id="preloader">
      <div class="preloader">
         <span></span>
         <span></span>
      </div>
   </div>
   <!-- preloader end  -->

   <!-- Begin magic cursor -->
   <div id="magic-cursor" class="cursor-black-bg">
      <div id="ball"></div>
   </div>
   <!-- End magic cursor -->

   <!-- back to top start -->
   <div class="back-to-top-wrapper">
      <button id="back_to_top" type="button" class="back-to-top-btn">
         <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M11 6L6 1L1 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
            stroke-linejoin="round" />
         </svg>
      </button>
   </div>
   <!-- back to top end -->

  <!-- tp-offcanvus-area-start -->
   <div class="tp-offcanvas-area">
      <div class="tp-offcanvas">
         <div class="tp-offcanvas-top d-flex align-items-center justify-content-between">
            <div class="tp-offcanvas-logo">
               <a href="/">
                  <img class="logo-1" data-width="140" src="/images/actas/logo-alcaldia.png" alt="">
                  <img class="logo-2" data-width="140" src="/images/actas/logo-alcaldia.png" alt="">
               </a>
            </div>
            <div class="tp-offcanvas-close-btn">
               <button class="close-btn">
                  <svg width="37" height="38" viewBox="0 0 37 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                     <path d="M9.19141 9.80762L27.5762 28.1924" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                     <path d="M9.19141 28.1924L27.5762 9.80761" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
               </button>
            </div>
         </div>
         <div class="tp-offcanvas-content d-none d-xl-block">
            <h3 class="tp-offcanvas-title">Bienvenido</h3>
            <p>Sistema de trámites de la Alcaldía de Puerto Boyacá.</p>
         </div>
         <div class="tp-offcanvas-menu d-xl-none">
            <nav></nav>
         </div>
         <div class="tp-offcanvas-gallery d-none d-xl-block">
            <div class="row gx-2">
               <div class="col-md-3 col-3">
                  <div class="tp-offcanvas-gallery-img fix">
                     <a class="popup-image" href="/landing/img/oficial/hero/thumb.jpg"><img src="/landing/img/oficial/hero/thumb.jpg" alt=""></a>
                  </div>
               </div>
               <div class="col-md-3 col-3">
                  <div class="tp-offcanvas-gallery-img fix">
                     <a class="popup-image" href="/landing/img/oficial/hero/thumb-2.jpg"><img src="/landing/img/oficial/hero/thumb-2.jpg" alt=""></a>
                  </div>
               </div>
               <div class="col-md-3 col-3">
                  <div class="tp-offcanvas-gallery-img fix">
                     <a class="popup-image" href="/landing/img/oficial/hero/thumb-3.jpg"><img src="/landing/img/oficial/hero/thumb-3.jpg" alt=""></a>
                  </div>
               </div>
               <div class="col-md-3 col-3">
                  <div class="tp-offcanvas-gallery-img fix">
                     <a class="popup-image" href="/landing/img/oficial/hero/thumb-4.jpg"><img src="/landing/img/oficial/hero/thumb-4.jpg" alt=""></a>
                  </div>
               </div>
            </div>
         </div>
         <div class="tp-offcanvas-contact">
            <h3 class="tp-offcanvas-title sm">Información</h3>
            <ul>
               <li><a href="https://www.puertoboyaca-boyaca.gov.co" target="_blank" rel="noopener">Puerto Boyacá, Boyacá</a></li>
               <li><a href="mailto:contactenos@@puertoboyaca-boyaca.gov.co">contactenos@@puertoboyaca-boyaca.gov.co</a></li>
               <li><a href="https://www.puertoboyaca-boyaca.gov.co" target="_blank" rel="noopener">Alcaldía Municipal de Puerto Boyacá</a></li>
            </ul>
         </div>
         <div class="tp-offcanvas-social">
            <h3 class="tp-offcanvas-title sm">Síguenos</h3>
            <ul>
               <li>
                  <a href="https://www.puertoboyaca-boyaca.gov.co" target="_blank" rel="noopener">
                     <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.25 1.5H4.75C2.95507 1.5 1.5 2.95507 1.5 4.75V11.25C1.5 13.0449 2.95507 14.5 4.75 14.5H11.25C13.0449 14.5 14.5 13.0449 14.5 11.25V4.75C14.5 2.95507 13.0449 1.5 11.25 1.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M10.6016 7.5907C10.6818 8.13166 10.5894 8.68414 10.3375 9.16955C10.0856 9.65497 9.68711 10.0486 9.19862 10.2945C8.71014 10.5404 8.15656 10.6259 7.61663 10.5391C7.0767 10.4522 6.57791 10.1972 6.19121 9.81055C5.80451 9.42385 5.54959 8.92506 5.46271 8.38513C5.37583 7.8452 5.46141 7.29163 5.70728 6.80314C5.95315 6.31465 6.34679 5.91613 6.83221 5.66425C7.31763 5.41238 7.87011 5.31998 8.41107 5.4002C8.96287 5.48202 9.47372 5.73915 9.86817 6.1336C10.2626 6.52804 10.5197 7.0389 10.6016 7.5907Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M11.5742 4.42578H11.5842" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                     </svg>
                  </a>
               </li>
               <li>
                  <a href="https://www.puertoboyaca-boyaca.gov.co" target="_blank" rel="noopener">
                     <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.50589 12.7494C4.57662 16.336 9.16278 17.5648 12.7494 15.4941C14.2113 14.65 15.2816 13.388 15.8962 11.9461C16.7895 9.85066 16.7208 7.37526 15.4941 5.25063C14.2674 3.12599 12.1581 1.82872 9.89669 1.55462C8.34063 1.366 6.71259 1.66183 5.25063 2.50589C1.66403 4.57662 0.435172 9.16278 2.50589 12.7494Z" stroke="currentColor" stroke-width="1.5" />
                        <path d="M12.7127 15.4292C12.7127 15.4292 12.0086 10.4867 10.5011 7.87559C8.99362 5.26451 5.28935 2.57155 5.28935 2.57155M5.68449 15.6124C6.79553 12.2606 12.34 8.54524 16.3975 9.43537M12.311 2.4082C11.1953 5.72344 5.75732 9.38453 1.71875 8.58915" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                     </svg>
                  </a>
               </li>
               <li>
                  <a href="https://www.puertoboyaca-boyaca.gov.co" target="_blank" rel="noopener">
                     <svg width="18" height="11" viewBox="0 0 18 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 5.5715H6.33342C7.62867 5.5715 8.61917 6.56199 8.61917 7.85725C8.61917 9.15251 7.62867 10.143 6.33342 10.143H1.76192C1.30477 10.143 1 9.83823 1 9.38108V1.76192C1 1.30477 1.30477 1 1.76192 1H5.5715C6.86676 1 7.85725 1.99049 7.85725 3.28575C7.85725 4.58101 6.86676 5.5715 5.5715 5.5715H1Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10"></path>
                        <path d="M10.9062 7.09454H17.0016C17.0016 5.41832 15.6301 4.04688 13.9539 4.04688C12.2777 4.04688 10.9062 5.41832 10.9062 7.09454ZM10.9062 7.09454C10.9062 8.77076 12.2777 10.1422 13.9539 10.1422H15.2492" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M16.1125 1.44434H11.668" stroke="currentColor" stroke-width="1.2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                     </svg>
                  </a>
               </li>
               <li>
                  <a href="https://www.puertoboyaca-boyaca.gov.co" target="_blank" rel="noopener">
                     <svg width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.75 13H5.25C3 13 1.5 11.5 1.5 9.25V4.75C1.5 2.5 3 1 5.25 1H12.75C15 1 16.5 2.5 16.5 4.75V9.25C16.5 11.5 15 13 12.75 13Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M8.70676 5.14837L10.8006 6.40465C11.5543 6.90716 11.5543 7.66093 10.8006 8.16344L8.70676 9.41972C7.86923 9.92224 7.19922 9.50348 7.19922 8.5822V6.06964C7.19922 4.98086 7.86923 4.64585 8.70676 5.14837Z" fill="currentColor" />
                     </svg>
                  </a>
               </li>
            </ul>
         </div>
      </div>
   </div>
   <div class="body-overlay"></div>
   <!-- tp-offcanvus-area-end -->

   <!-- offcanvas 2 start -->
   <div class="tp-offcanvas-2-area p-relative">
      <div class="offcanvas-bg"></div>
      <div class="tp-offcanvas-2-wrapper offcanvas-menu">
         <div class="tp-offcanvas-2-left">
            <div class="tp-header-logo d-flex justify-content-between align-items-center mb-50">
               <a href="/">
                  <img class="logo-1" data-width="170" src="/images/actas/logo-alcaldia.png" alt="">
                  <img class="logo-2" data-width="170" src="/images/actas/logo-alcaldia.png" alt="">
               </a>
               <span class="hamburger-close-btn">
                  <svg width="37" height="38" viewBox="0 0 37 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                     <path d="M9.19141 9.80762L27.5762 28.1924" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                     <path d="M9.19141 28.1924L27.5762 9.80761" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
               </span>
            </div>
            <div class="tp-offcanvas-menu counter-row">
               <nav></nav>
            </div>
            <span class="hamburger-close-btn hamburger-mobile-close-btn d-md-none">CLOSE</span>
         </div>
      </div>
   </div>
   <!-- offcanvas 2 end -->

   <header>

      <!-- header area start -->
      <div class="tp-header-area ca-header-style tp-header-spacing header-transparent">
         <div class="container">
            <div class="row align-items-center">
               <div class="col-xl-3 col-6">
                  <div class="tp-header-logo">
                     <a href="/">
                        <img class="logo-1" data-width="140" src="/images/actas/logo-alcaldia.png" alt="">
                        <img class="logo-2" data-width="140" src="/images/actas/logo-alcaldia.png" alt="">
                     </a>
                  </div>
               </div>
               <div class="col-xl-6 d-none d-xl-block">
                  <div class="tp-main-menu d-flex justify-content-center">
                     <nav class="tp-mobile-menu-active">
                        <ul>
                           <li><a href="/">Inicio</a></li>
                           <li><a href="#modulos">Módulos</a></li>
                           <li><a href="#faq">Preguntas</a></li>
                           <li><a href="/admin">Ingresar</a></li>
                        </ul>
                     </nav>
                  </div>
               </div>
               <div class="col-xl-3 col-6">
                  <div class="tp-header-right d-flex justify-content-end align-items-center">
                     <a class="tp-btn tp-btn-border-black tp-ff-inter d-none d-sm-inline-block" href="/admin">
                        <span>
                           <span class="text-1">Ingresar</span>
                           <span class="text-2">Ingresar</span>
                        </span>
                        <i>
                           <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path d="M0.21967 9.40717C-0.0732232 9.70006 -0.0732232 10.1749 0.21967 10.4678C0.512563 10.7607 0.987437 10.7607 1.28033 10.4678L0.21967 9.40717ZM10.6875 0.75C10.6875 0.335786 10.3517 2.97145e-09 9.9375 1.50485e-07L3.1875 -2.70983e-07C2.77329 -2.70983e-07 2.4375 0.335786 2.4375 0.75C2.4375 1.16421 2.77329 1.5 3.1875 1.5H9.1875V7.5C9.1875 7.91421 9.52329 8.25 9.9375 8.25C10.3517 8.25 10.6875 7.91421 10.6875 7.5L10.6875 0.75ZM0.75 9.9375L1.28033 10.4678L10.4678 1.28033L9.9375 0.75L9.40717 0.21967L0.21967 9.40717L0.75 9.9375Z" fill="currentColor" />
                           </svg>
                           <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path d="M0.21967 9.40717C-0.0732232 9.70006 -0.0732232 10.1749 0.21967 10.4678C0.512563 10.7607 0.987437 10.7607 1.28033 10.4678L0.21967 9.40717ZM10.6875 0.75C10.6875 0.335786 10.3517 2.97145e-09 9.9375 1.50485e-07L3.1875 -2.70983e-07C2.77329 -2.70983e-07 2.4375 0.335786 2.4375 0.75C2.4375 1.16421 2.77329 1.5 3.1875 1.5H9.1875V7.5C9.1875 7.91421 9.52329 8.25 9.9375 8.25C10.3517 8.25 10.6875 7.91421 10.6875 7.5L10.6875 0.75ZM0.75 9.9375L1.28033 10.4678L10.4678 1.28033L9.9375 0.75L9.40717 0.21967L0.21967 9.40717L0.75 9.9375Z" fill="currentColor" />
                           </svg>
                        </i>
                     </a>
                     <button class="tp-menu-bar tp-header-sidebar-btn tp-header-sidebar-btn-bg ml-10">
                        <span></span>
                        <span></span>
                     </button>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- header area end -->

      <!-- sticky-menu-area -->
      <button id="header-sticky" class="hamburger-open-btn tp-header-sidebar-btn hamburger-sticky-menu">
         <span></span>
         <span></span>
      </button>
      <!-- sticky-menu-end -->

   </header>

   <div id="smooth-wrapper">
      <div id="smooth-content">
      
         <main>
            
            <!-- ca-hero-area-start -->
            <div class="ca-hero-area ca-hero-spacing" data-bg-color="#f6f6f6">
               <div class="container">
                  <div class="row">
                     <div class="col-xl-4 col-lg-5 col-md-5">
                        <div class="ca-hero-left pt-65 pb-30">
                           <div class="ca-hero-video p-relative">
                              <img class="img-cover w-100" src="/landing/img/alcaldia2/foto-reunion.jpg" alt="Equipo de la Alcaldía de Puerto Boyacá" style="border-radius:10px;">
                           </div>
                           <div class="ca-hero-service">
                              <ul>
                                 <li>
                                    <a href="#modulos"><span class="explore-text" data-text="Afiliaciones ARL">Afiliaciones ARL</span></a>
                                 </li>
                                 <li>
                                    <a href="#modulos"><span class="explore-text" data-text="Actas de Necesidad">Actas de Necesidad</span></a>
                                 </li>
                                 <li>
                                    <a href="#modulos"><span class="explore-text" data-text="Contratación">Contratación</span></a>
                                 </li>
                                 <li>
                                    <a href="#modulos"><span class="explore-text" data-text="Plan de Adquisiciones">Plan de Adquisiciones</span></a>
                                 </li>
                                 <li>
                                    <a href="#modulos"><span class="explore-text" data-text="Solicitudes BPIM">Solicitudes BPIM</span></a>
                                 </li>
                              </ul>
                           </div>
                        </div>
                     </div>
                     <div class="col-xl-5 col-lg-7 col-md-7">
                        <div class="ca-hero-title-wrap pt-50 pb-105">
                           <h2 class="ca-hero-title cd-headline clip tp_title_anim mb-55">
                              Trámites<br>
                              <span class="cd-words-wrapper">
                                 <b class="is-visible">Afiliaciones</b>
                                 <b class="app">Actas</b>
                                 <b>Contratación</b>
                              </span>
                              <br>
                              en línea
                           </h2>
                           <a class="tp-btn tp-btn-norotate ca-hero-btn tp-ff-inter" href="/admin">
                              <span>
                                 <span class="text-1">Ingresar al sistema</span>
                                 <span class="text-2">Ingresar al sistema</span>
                              </span>
                              <i>
                                 <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0.21967 9.40717C-0.0732232 9.70006 -0.0732232 10.1749 0.21967 10.4678C0.512563 10.7607 0.987437 10.7607 1.28033 10.4678L0.21967 9.40717ZM10.6875 0.75C10.6875 0.335786 10.3517 2.97145e-09 9.9375 1.50485e-07L3.1875 -2.70983e-07C2.77329 -2.70983e-07 2.4375 0.335786 2.4375 0.75C2.4375 1.16421 2.77329 1.5 3.1875 1.5H9.1875V7.5C9.1875 7.91421 9.52329 8.25 9.9375 8.25C10.3517 8.25 10.6875 7.91421 10.6875 7.5L10.6875 0.75ZM0.75 9.9375L1.28033 10.4678L10.4678 1.28033L9.9375 0.75L9.40717 0.21967L0.21967 9.40717L0.75 9.9375Z" fill="currentColor" />
                                 </svg>
                                 <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0.21967 9.40717C-0.0732232 9.70006 -0.0732232 10.1749 0.21967 10.4678C0.512563 10.7607 0.987437 10.7607 1.28033 10.4678L0.21967 9.40717ZM10.6875 0.75C10.6875 0.335786 10.3517 2.97145e-09 9.9375 1.50485e-07L3.1875 -2.70983e-07C2.77329 -2.70983e-07 2.4375 0.335786 2.4375 0.75C2.4375 1.16421 2.77329 1.5 3.1875 1.5H9.1875V7.5C9.1875 7.91421 9.52329 8.25 9.9375 8.25C10.3517 8.25 10.6875 7.91421 10.6875 7.5L10.6875 0.75ZM0.75 9.9375L1.28033 10.4678L10.4678 1.28033L9.9375 0.75L9.40717 0.21967L0.21967 9.40717L0.75 9.9375Z" fill="currentColor" />
                                 </svg>
                              </i>
                           </a>
                        </div>
                     </div>
                     <div class="col-xl-3 col-lg-5">
                        <div class="ca-hero-dec ml-60 pb-30">
                           <p>
                              <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                 <path d="M0 1H13V14" stroke="currentColor" stroke-width="2" />
                              </svg>
                              Plataforma oficial de la Alcaldía de<br>
                              Puerto Boyacá para gestionar en línea<br>
                              afiliaciones ARL, actas de necesidad,<br>
                              contratación, plan de adquisiciones y<br>
                              solicitudes BPIM.</p>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="ca-hero-thumb fix scale-up-img">
                  <img data-speed="0.4" class="img-cover scale-up" src="/landing/img/oficial/hero/thumb.jpg" alt="">
               </div>
            </div>
            <!-- ca-hero-area-end -->

            <!-- ca-about-area-start -->
            <div class="ca-about-area pt-145 pb-150">
               <div class="container">
                  <div class="row justify-content-center">
                     <div class="col-xxl-10">
                        <div class="ca-about-title-wrap text-center">
                           <h2 class="ca-section-title ca-about-title reveal-text mb-50">En la Alcaldía de Puerto Boyacá no solo digitalizamos<br>
                              trámites: integramos en un solo lugar la gestión de
                              afiliaciones, actas, contratación y proyectos,<br>
                              con trazabilidad, aprobación en línea y<br>
                              documentos verificables por QR.</h2>
                           <div class="tp_fade_anim" data-delay=".4" data-fade-from="bottom" data-ease="bounce">
                              <a href="#modulos" class="tp-btn tp-btn-xl tp-btn-grey tp-btn-switch-animation">
                                 <span class="d-flex align-items-center justify-content-center">
                                    <span class="btn-text">Conocer los módulos</span>
                                    <span class="btn-icon">
                                       <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                          <path d="M1 6.36401C0.447715 6.36401 1.67492e-07 6.81173 1.19209e-07 7.36401C7.0927e-08 7.9163 0.447715 8.36401 1 8.36401L1 7.36401L1 6.36401ZM16.7071 8.07112C17.0976 7.6806 17.0976 7.04743 16.7071 6.65691L10.3431 0.292948C9.95262 -0.0975769 9.31946 -0.0975769 8.92893 0.292947C8.53841 0.683472 8.53841 1.31664 8.92893 1.70716L14.5858 7.36401L8.92893 13.0209C8.53841 13.4114 8.53841 14.0446 8.92893 14.4351C9.31946 14.8256 9.95262 14.8256 10.3431 14.4351L16.7071 8.07112ZM1 7.36401L1 8.36401L16 8.36401L16 7.36401L16 6.36402L1 6.36401L1 7.36401Z" fill="currentColor" />
                                       </svg>
                                    </span>
                                    <span class="btn-icon">
                                       <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                          <path d="M1 6.36401C0.447715 6.36401 1.67492e-07 6.81173 1.19209e-07 7.36401C7.0927e-08 7.9163 0.447715 8.36401 1 8.36401L1 7.36401L1 6.36401ZM16.7071 8.07112C17.0976 7.6806 17.0976 7.04743 16.7071 6.65691L10.3431 0.292948C9.95262 -0.0975769 9.31946 -0.0975769 8.92893 0.292947C8.53841 0.683472 8.53841 1.31664 8.92893 1.70716L14.5858 7.36401L8.92893 13.0209C8.53841 13.4114 8.53841 14.0446 8.92893 14.4351C9.31946 14.8256 9.95262 14.8256 10.3431 14.4351L16.7071 8.07112ZM1 7.36401L1 8.36401L16 8.36401L16 7.36401L16 6.36402L1 6.36401L1 7.36401Z" fill="currentColor" />
                                       </svg>
                                    </span>
                                 </span> 
                              </a>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- ca-about-area-end -->

            <!-- (sección de asociados eliminada) -->

            <!-- ca-portfolio-area-start -->
            <div id="modulos" class="ca-portfolio-area portfolio-area pt-160 pb-130">
               <div class="container">
                  <div class="row">
                     <div class="col-xxl-6 col-xl-6 offset-xxl-3 offset-xl-4">
                        <div class="ca-portfolio-main-title-wrap">
                           <h2 class="ca-portfolio-main-title tp-ff-sequel-bold-head portfolio-text">Nuestros
                              <span>Módulos
                                 <svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10.1806 0.652913C10.5028 -0.217705 11.7342 -0.217705 12.0563 0.652913L14.4701 7.17599C14.5714 7.44971 14.7872 7.66552 15.0609 7.7668L21.584 10.1806C22.4546 10.5027 22.4546 11.7341 21.584 12.0563L15.0609 14.47C14.7872 14.5713 14.5714 14.7871 14.4701 15.0608L12.0563 21.5839C11.7342 22.4545 10.5028 22.4545 10.1806 21.5839L7.76686 15.0608C7.66558 14.7871 7.44977 14.5713 7.17605 14.47L0.652974 12.0563C-0.217644 11.7341 -0.217644 10.5027 0.652974 10.1806L7.17605 7.7668C7.44977 7.66552 7.66558 7.44971 7.76686 7.17599L10.1806 0.652913Z" fill="currentColor" />
                                 </svg>
                              </span>
                              <br> del sistema</h2>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                        <div class="ca-portfolio-item tp-hover-item mb-30">
                           <a href="/admin" class="ca-portfolio-thumb mb-15 p-relative fix d-block">
                              <div class="tp-hover-img" data-displacement="/cunnet/assets/img/imghover/stripe.png" data-intensity="0.2" data-speedin="1" data-speedout="1">
                                 <img class="w-100" src="/landing/img/oficial/hero/thumb-2.jpg" alt="">
                              </div>
                              <div class="ca-portfolio-btn">
                                 <div class="p-relative d-inline-block">
                                    <img src="/cunnet/assets/img/portfolio/ca/shape.png" alt="">
                                    <span class="text">Abrir</span>
                                 </div>
                              </div>
                           </a>
                           <div class="ca-portfolio-content d-flex justify-content-between">
                              <h5 class="ca-portfolio-title mb-0"><a href="/admin" class="common-underline">Afiliaciones ARL</a></h5>
                              <span class="ca-portfolio-date tp-ff-sequel-medium">/ 2025</span>
                           </div>
                        </div>
                     </div>
                     <div class="col-xxl-4 col-xl-4 offset-xxl-5 offset-xl-4 col-lg-6 col-md-6">
                        <div class="ca-portfolio-item mb-30 ca-portfolio-item-2 mt-110 tp-hover-item">
                           <a href="/admin" class="ca-portfolio-thumb mb-15 p-relative fix d-block">
                              <div class="tp-hover-img" data-displacement="/cunnet/assets/img/imghover/stripe.png" data-intensity="0.2" data-speedin="1" data-speedout="1">
                                 <img class="w-100" src="/landing/img/oficial/hero/thumb-3.jpg" alt="">
                              </div>
                              <div class="ca-portfolio-btn">
                                 <div class="p-relative d-inline-block">
                                    <img src="/cunnet/assets/img/portfolio/ca/shape.png" alt="">
                                    <span class="text">Abrir</span>
                                 </div>
                              </div>
                           </a>
                           <div class="ca-portfolio-content d-flex justify-content-between">
                              <h5 class="ca-portfolio-title mb-0"><a href="/admin" class="common-underline">Actas de Necesidad</a></h5>
                              <span class="ca-portfolio-date tp-ff-sequel-medium">/ 2025</span>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-xl-4 offset-xl-2 col-lg-6 col-md-6">
                        <div class="ca-portfolio-item ca-portfolio-item-3 mb-30 tp-hover-item">
                           <a href="/admin" class="ca-portfolio-thumb mb-15 p-relative fix d-block">
                              <div class="tp-hover-img" data-displacement="/cunnet/assets/img/imghover/stripe.png" data-intensity="0.2" data-speedin="1" data-speedout="1">
                                 <img class="w-100" src="/landing/img/oficial/hero/thumb-4.jpg" alt="">
                              </div>
                              <div class="ca-portfolio-btn">
                                 <div class="p-relative d-inline-block">
                                    <img src="/cunnet/assets/img/portfolio/ca/shape.png" alt="">
                                    <span class="text">Abrir</span>
                                 </div>
                              </div>
                           </a>
                           <div class="ca-portfolio-content d-flex justify-content-between">
                              <h5 class="ca-portfolio-title mb-0"><a href="/admin" class="common-underline">Contratación</a></h5>
                              <span class="ca-portfolio-date tp-ff-sequel-medium">/ 2025</span>
                           </div>
                        </div>
                     </div>
                     <div class="col-xl-3 offset-xl-1 col-lg-6 col-md-6">
                        <div class="ca-portfolio-item ca-portfolio-item-4 mt-110 mb-30 tp-hover-item">
                           <a href="/admin" class="ca-portfolio-thumb mb-15 p-relative fix d-block">
                              <div class="tp-hover-img" data-displacement="/cunnet/assets/img/imghover/stripe.png" data-intensity="0.2" data-speedin="1" data-speedout="1">
                                 <img class="w-100" src="/landing/img/oficial/hero/thumb-5.jpg" alt="">
                              </div>
                              <div class="ca-portfolio-btn">
                                 <div class="p-relative d-inline-block">
                                    <img src="/cunnet/assets/img/portfolio/ca/shape.png" alt="">
                                    <span class="text">Abrir</span>
                                 </div>
                              </div>
                           </a>
                           <div class="ca-portfolio-content d-flex justify-content-between">
                              <h5 class="ca-portfolio-title mb-0"><a href="/admin" class="common-underline">Plan de Adquisiciones</a></h5>
                              <span class="ca-portfolio-date tp-ff-sequel-medium">/ 2025</span>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-xl-4 col-lg-6 col-md-6">
                        <div class="ca-portfolio-item ca-portfolio-item-3 mb-30 tp-hover-item">
                           <a href="/admin" class="ca-portfolio-thumb mb-15 p-relative fix d-block">
                              <div class="tp-hover-img" data-displacement="/cunnet/assets/img/imghover/stripe.png" data-intensity="0.2" data-speedin="1" data-speedout="1">
                                 <img class="w-100" src="/landing/img/oficial/hero/thumb-6.jpg" alt="">
                              </div>
                              <div class="ca-portfolio-btn">
                                 <div class="p-relative d-inline-block">
                                    <img src="/cunnet/assets/img/portfolio/ca/shape.png" alt="">
                                    <span class="text">Abrir</span>
                                 </div>
                              </div>
                           </a>
                           <div class="ca-portfolio-content d-flex justify-content-between">
                              <h5 class="ca-portfolio-title mb-0"><a href="/admin" class="common-underline">Solicitudes BPIM</a></h5>
                              <span class="ca-portfolio-date tp-ff-sequel-medium">/ 2025</span>
                           </div>
                        </div>
                     </div>
                     <div class="col-xl-4 offset-xl-4 col-lg-6 col-md-6">
                        <div class="ca-portfolio-item ca-portfolio-item-6 mb-30 tp-hover-item">
                           <a href="/admin" class="ca-portfolio-thumb mb-15 p-relative fix d-block">
                              <div class="tp-hover-img" data-displacement="/cunnet/assets/img/imghover/stripe.png" data-intensity="0.2" data-speedin="1" data-speedout="1">
                                 <img class="w-100" src="/landing/img/oficial/hero/thumb-8.jpg" alt="">
                              </div>
                              <div class="ca-portfolio-btn">
                                 <div class="p-relative d-inline-block">
                                    <img src="/cunnet/assets/img/portfolio/ca/shape.png" alt="">
                                    <span class="text">Abrir</span>
                                 </div>
                              </div>
                           </a>
                           <div class="ca-portfolio-content d-flex justify-content-between">
                              <h5 class="ca-portfolio-title mb-0"><a href="/admin" class="common-underline">Verificación de Documentos</a></h5>
                              <span class="ca-portfolio-date tp-ff-sequel-medium">/ 2025</span>
                           </div>
                        </div>
                     </div>
                     <div class="col-xxl-3 col-xl-4 offset-xxl-3 offset-xl-2 col-lg-6 col-md-6">
                        <div class="ca-portfolio-item ca-portfolio-item-3 mb-30 tp-hover-item">
                           <a href="/admin" class="ca-portfolio-thumb mb-15 p-relative fix d-block">
                              <div class="tp-hover-img" data-displacement="/cunnet/assets/img/imghover/stripe.png" data-intensity="0.2" data-speedin="1" data-speedout="1">
                                 <img class="w-100" src="/landing/img/oficial/hero/thumb-7.jpg" alt="">
                              </div>
                              <div class="ca-portfolio-btn">
                                 <div class="p-relative d-inline-block">
                                    <img src="/cunnet/assets/img/portfolio/ca/shape.png" alt="">
                                    <span class="text">Abrir</span>
                                 </div>
                              </div>
                           </a>
                           <div class="ca-portfolio-content d-flex justify-content-between">
                              <h5 class="ca-portfolio-title mb-0"><a href="/admin" class="common-underline">Reportes y Estadísticas</a></h5>
                              <span class="ca-portfolio-date tp-ff-sequel-medium">/ 2025</span>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- ca-portfolio-area-end -->

            <div class="ca-testimonial-spacing" data-bg-color="#09090b">
               <!-- ca-testimonial-area-start -->
               <div class="ca-testimonial-area pt-135 pb-155">
                  <div class="container">
                     <div class="row">
                        <div class="col-xl-5">
                           <div class="ca-testimonial-title-wrap mb-30">
                              <div class="ca-testimonial-review mb-15">
                                 <h3 class="ca-testimonial-ratings tp-ff-inter p-relative reveal-text">4.8 
                                    <svg width="18" height="17" viewBox="0 0 18 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                       <path d="M8.55951 0L10.5801 6.21885H17.119L11.829 10.0623L13.8496 16.2812L8.55951 12.4377L3.26944 16.2812L5.29007 10.0623L9.53674e-07 6.21885H6.53888L8.55951 0Z" fill="#F9A811" />
                                    </svg>
                                 </h3>
                                 <span class="ca-testimonial-review-count">Alcaldía de Puerto Boyacá</span>
                              </div>
                              <h2 class="ca-section-title fs-52 text-white mb-115 reveal-text">Un solo lugar para<br> todos los trámites<br> de la Alcaldía.</h2>
                              <div class="ca-testimonial-navigation">
                                 <span class="ca-testimonial-arrow-prev"><i class="fa-solid fa-arrow-left"></i></span>
                                 <span class="ca-testimonial-arrow-next"><i class="fa-solid fa-arrow-right"></i></span>
                              </div>
                           </div>
                        </div>
                        <div class="col-xxl-6 col-xl-7">
                           <div class="p-relative">
                              <span class="ca-testimonial-bg-transparent"></span>
                              <div class="ca-testimonial-slider-wrap p-relative mb-30">
                                 <span class="ca-testimonial-pagination"></span>
                                 <div class="swiper ca-testimonial-slider-active">
                                    <div class="swiper-wrapper">
                                       <div class="swiper-slide">
                                          <div class="ca-testimonial-item text-center">
                                             <span class="ca-testimonial-reviewed d-block">Puerto Boyacá</span>
                                             <img class="mb-30" src="/images/actas/logo-alcaldia.png" alt="">
                                             <p class="ca-testimonial-comment mb-30">“ El sistema de trámites centraliza afiliaciones,
                                                actas, contratación y proyectos, con aprobación
                                                en línea, notificaciones por correo y documentos
                                                verificables por código QR. ”</p>
                                             <div class="ca-testimonial-author-name">
                                                <b>Secretaría General</b>
                                                <span class="d-block">Alcaldía de Puerto Boyacá</span>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="swiper-slide">
                                          <div class="ca-testimonial-item text-center">
                                             <span class="ca-testimonial-reviewed d-block">Puerto Boyacá</span>
                                             <img class="mb-30" src="/images/actas/logo-alcaldia.png" alt="">
                                             <p class="ca-testimonial-comment mb-30">“ El sistema de trámites centraliza afiliaciones,
                                                actas, contratación y proyectos, con aprobación
                                                en línea, notificaciones por correo y documentos
                                                verificables por código QR. ”</p>
                                             <div class="ca-testimonial-author-name">
                                                <b>Secretaría General</b>
                                                <span class="d-block">Alcaldía de Puerto Boyacá</span>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- ca-testimonial-area-end -->

               <!-- ca-team-area-start -->
               <div class="ca-team-area pb-120">
                  <div class="container">
                     <div class="ca-team-border pt-150"></div>
                     <div class="row">
                        <div class="col-lg-5">
                           <div class="ca-team-subtitle-wrap mb-30">
                              <span class="ca-team-subtitle text-white"><span>[</span> Beneficios <span>]</span></span>
                           </div>
                        </div>
                        <div class="col-lg-7">
                           <div class="ca-team-title-wrap mb-50">
                              <h2 class="ca-section-title fs-100 text-white lh-1 reveal-text">Beneficios del<br> sistema</h2>
                           </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                           <div class="ca-team-item tp-hover-item mb-30 tp_fade_anim" data-delay=".3">
                              <a href="/admin" class="ca-portfolio-thumb mb-20 p-relative fix d-block">
                                 <div class="tp-hover-img" data-displacement="/cunnet/assets/img/imghover/fluid.jpg" data-intensity="0.2" data-speedin="1" data-speedout="1">
                                    <img class="w-100" src="/landing/img/oficial/hero/thumb-5.jpg" alt="">
                                 </div>
                              </a>
                              <div class="ca-team-content">
                                 <h5 class="ca-team-title tp-ff-inter text-white mb-0"><a href="/admin" class="common-underline">Aprobación en línea</a></h5>
                                 <span>Flujo de revisión y aprobación</span>
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                           <div class="ca-team-item tp-hover-item mb-30 tp_fade_anim" data-delay=".5">
                              <a href="/admin" class="ca-portfolio-thumb mb-20 p-relative fix d-block">
                                 <div class="tp-hover-img" data-displacement="/cunnet/assets/img/imghover/fluid.jpg" data-intensity="0.2" data-speedin="1" data-speedout="1">
                                    <img class="w-100" src="/landing/img/oficial/hero/thumb-6.jpg" alt="">
                                 </div>
                              </a>
                              <div class="ca-team-content">
                                 <h5 class="ca-team-title tp-ff-inter text-white mb-0"><a href="/admin" class="common-underline">Verificación por QR</a></h5>
                                 <span>Documentos auténticos</span>
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                           <div class="ca-team-item tp-hover-item mb-30 tp_fade_anim" data-delay=".7">
                              <a href="/admin" class="ca-portfolio-thumb mb-20 p-relative fix d-block">
                                 <div class="tp-hover-img" data-displacement="/cunnet/assets/img/imghover/fluid.jpg" data-intensity="0.2" data-speedin="1" data-speedout="1">
                                    <img class="w-100" src="/landing/img/oficial/hero/thumb-8.jpg" alt="">
                                 </div>
                              </a>
                              <div class="ca-team-content">
                                 <h5 class="ca-team-title tp-ff-inter text-white mb-0"><a href="/admin" class="common-underline">Notificaciones</a></h5>
                                 <span>Avisos por correo</span>
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                           <div class="ca-team-item tp-hover-item mb-30 tp_fade_anim" data-delay=".9">
                              <a href="/admin" class="ca-portfolio-thumb mb-20 p-relative fix d-block">
                                 <div class="tp-hover-img" data-displacement="/cunnet/assets/img/imghover/fluid.jpg" data-intensity="0.2" data-speedin="1" data-speedout="1">
                                    <img class="w-100" src="/landing/img/oficial/hero/thumb-7.jpg" alt="">
                                 </div>
                              </a>
                              <div class="ca-team-content">
                                 <h5 class="ca-team-title tp-ff-inter text-white mb-0">
                                    <a href="/admin" class="common-underline">Trazabilidad</a>
                                 </h5>
                                 <span>Auditoría y seguimiento</span>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- ca-team-area-end -->
            </div>

            <!-- ca-faq-area-start -->
            <div id="faq" class="ca-faq-area pt-135 pb-145">
               <div class="container">
                  <div class="row">
                     <div class="col-lg-5">
                        <div class="ca-faq-title-wrap mb-40 tp_fade_anim" data-delay=".3">
                           <span class="ca-team-subtitle text-uppercase d-block mb-15"><span>[ </span>Preguntas<span> ]</span></span>
                           <img class="mb-10" src="/images/actas/logo-alcaldia.png" alt="Escudo Alcaldía de Puerto Boyacá" style="max-width:120px;height:auto;">
                           <h2 class="ca-section-title mb-15">Preguntas frecuentes</h2>
                           <p class="tp-faq-dec mb-35">Resuelve tus dudas sobre el sistema</p>
                           <a href="/admin" class="tp-btn tp-btn-xl tp-btn-grey tp-btn-switch-animation">
                              <span class="d-flex align-items-center justify-content-center">
                                 <span class="btn-text">Ingresar</span>
                                 <span class="btn-icon">
                                    <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                       <path d="M1 6.36401C0.447715 6.36401 1.67492e-07 6.81173 1.19209e-07 7.36401C7.0927e-08 7.9163 0.447715 8.36401 1 8.36401L1 7.36401L1 6.36401ZM16.7071 8.07112C17.0976 7.6806 17.0976 7.04743 16.7071 6.65691L10.3431 0.292948C9.95262 -0.0975769 9.31946 -0.0975769 8.92893 0.292947C8.53841 0.683472 8.53841 1.31664 8.92893 1.70716L14.5858 7.36401L8.92893 13.0209C8.53841 13.4114 8.53841 14.0446 8.92893 14.4351C9.31946 14.8256 9.95262 14.8256 10.3431 14.4351L16.7071 8.07112ZM1 7.36401L1 8.36401L16 8.36401L16 7.36401L16 6.36402L1 6.36401L1 7.36401Z" fill="currentColor" />
                                    </svg>
                                 </span>
                                 <span class="btn-icon">
                                    <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                       <path d="M1 6.36401C0.447715 6.36401 1.67492e-07 6.81173 1.19209e-07 7.36401C7.0927e-08 7.9163 0.447715 8.36401 1 8.36401L1 7.36401L1 6.36401ZM16.7071 8.07112C17.0976 7.6806 17.0976 7.04743 16.7071 6.65691L10.3431 0.292948C9.95262 -0.0975769 9.31946 -0.0975769 8.92893 0.292947C8.53841 0.683472 8.53841 1.31664 8.92893 1.70716L14.5858 7.36401L8.92893 13.0209C8.53841 13.4114 8.53841 14.0446 8.92893 14.4351C9.31946 14.8256 9.95262 14.8256 10.3431 14.4351L16.7071 8.07112ZM1 7.36401L1 8.36401L16 8.36401L16 7.36401L16 6.36402L1 6.36401L1 7.36401Z" fill="currentColor" />
                                    </svg>
                                 </span>
                              </span> 
                           </a>
                        </div>
                     </div>
                     <div class="col-xl-7">
                        <div class="tp-faq ml-115">
                           <div class="accordion" id="accordionExample">
                              <div class="tp-faq-item tp_fade_anim" data-delay=".3">
                                 <h2 class="accordion-header">
                                    <button class="tp-faq-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">¿Qué es el sistema de trámites?</button>
                                 </h2>
                                 <div id="collapseOne" class="tp-faq-collapse collapse show" data-bs-parent="#accordionExample">
                                    <div class="tp-faq-body">
<p>Es la plataforma oficial de la Alcaldía de Puerto Boyacá para gestionar en línea afiliaciones ARL, actas de necesidad, contratación, plan de adquisiciones y solicitudes BPIM.</p>
</div>
                                 </div>
                              </div>
                              <div class="tp-faq-item tp_fade_anim" data-delay=".3">
                                 <h2 class="accordion-header">
                                    <button class="tp-faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">¿Qué módulos incluye?</button>
                                 </h2>
                                 <div id="collapseTwo" class="tp-faq-collapse collapse" data-bs-parent="#accordionExample">
                                    <div class="tp-faq-body">
<p>Incluye Afiliaciones ARL, Actas de Necesidad, Contratación, Plan de Adquisiciones, Solicitudes BPIM, verificación de documentos por QR y reportes.</p>
</div>
                                 </div>
                              </div>
                              <div class="tp-faq-item tp_fade_anim" data-delay=".3">
                                 <h2 class="accordion-header">
                                    <button class="tp-faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">¿Cómo se registra una solicitud?</button>
                                 </h2>
                                 <div id="collapseThree" class="tp-faq-collapse collapse" data-bs-parent="#accordionExample">
                                    <div class="tp-faq-body">
<p>Los funcionarios ingresan al panel con su usuario y registran la solicitud en el módulo correspondiente; el sistema guía el diligenciamiento paso a paso.</p>
</div>
                                 </div>
                              </div>
                              <div class="tp-faq-item tp_fade_anim" data-delay=".3">
                                 <h2 class="accordion-header">
                                    <button class="tp-faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">¿Cómo se aprueban las solicitudes?</button>
                                 </h2>
                                 <div id="collapseFour" class="tp-faq-collapse collapse" data-bs-parent="#accordionExample">
                                    <div class="tp-faq-body">
<p>Cada solicitud pasa por un flujo de revisión y aprobación. Solo los usuarios habilitados pueden aprobar, y queda registrado quién y cuándo lo hizo.</p>
</div>
                                 </div>
                              </div>
                              <div class="tp-faq-item tp_fade_anim" data-delay=".3">
                                 <h2 class="accordion-header">
                                    <button class="tp-faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">¿Cómo verifico la autenticidad de un documento?</button>
                                 </h2>
                                 <div id="collapseFive" class="tp-faq-collapse collapse" data-bs-parent="#accordionExample">
                                    <div class="tp-faq-body">
<p>Los documentos oficiales llevan un código QR; al escanearlo se abre una página pública que confirma su autenticidad y muestra sus datos.</p>
</div>
                                 </div>
                              </div>
                              <div class="tp-faq-item tp_fade_anim" data-delay=".3">
                                 <h2 class="accordion-header">
                                    <button class="tp-faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">¿Quién puede acceder al sistema?</button>
                                 </h2>
                                 <div id="collapseSix" class="tp-faq-collapse collapse" data-bs-parent="#accordionExample">
                                    <div class="tp-faq-body">
<p>El personal autorizado de cada dependencia. Los permisos se administran por roles, de modo que cada usuario ve solo lo que le corresponde.</p>
</div>
                                 </div>
                              </div>
                              <div class="tp-faq-item tp_fade_anim" data-delay=".3">
                                 <h2 class="accordion-header">
                                    <button class="tp-faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSaven" aria-expanded="false" aria-controls="collapseSaven">¿Cómo ingreso al sistema?</button>
                                 </h2>
                                 <div id="collapseSaven" class="tp-faq-collapse collapse" data-bs-parent="#accordionExample">
                                    <div class="tp-faq-body">
<p>Haz clic en “Ingresar”, inicia sesión con tu usuario institucional y, si olvidaste la contraseña, usa la opción de recuperación.</p>
</div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- ca-faq-area-end -->

            <!-- ca-cta-area-start -->
            <div class="ca-testimonial-spacing fix" data-bg-color="#09090b">
               <div class="ca-cta-area ca-cta-spacing pt-180 pb-120 p-relative z-index-1">
                  <div class="mil-scale-img ca-cta-scale" data-value-1="1.45" data-value-2="1">
                     <img class="ca-cta-shape" src="/cunnet/assets/img/cta/shape.png" alt="">
                  </div>
                  <div class="container">
                     <div class="row align-content-end">
                        <div class="col-lg-7">
                           <div class="ca-cta-title-wrap p-relative mb-40">
                              <h2 class="ca-section-title fs-100 text-white lh-1 mb-50 reveal-text">¿Listo para<br> empezar?</h2>
                              <div class="tp_fade_anim" data-delay=".4" data-fade-from="bottom" data-ease="bounce">
                                 <a class="tp-btn tp-btn-red tp-ff-inter" href="/admin">
                                    <span>
                                       <span class="text-1">Ingresar</span>
                                       <span class="text-2">Ingresar</span>
                                    </span>
                                    <i>
                                       <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                          <path d="M0.21967 9.40717C-0.0732232 9.70006 -0.0732232 10.1749 0.21967 10.4678C0.512563 10.7607 0.987437 10.7607 1.28033 10.4678L0.21967 9.40717ZM10.6875 0.75C10.6875 0.335786 10.3517 2.97145e-09 9.9375 1.50485e-07L3.1875 -2.70983e-07C2.77329 -2.70983e-07 2.4375 0.335786 2.4375 0.75C2.4375 1.16421 2.77329 1.5 3.1875 1.5H9.1875V7.5C9.1875 7.91421 9.52329 8.25 9.9375 8.25C10.3517 8.25 10.6875 7.91421 10.6875 7.5L10.6875 0.75ZM0.75 9.9375L1.28033 10.4678L10.4678 1.28033L9.9375 0.75L9.40717 0.21967L0.21967 9.40717L0.75 9.9375Z" fill="currentColor" />
                                       </svg>
                                       <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                          <path d="M0.21967 9.40717C-0.0732232 9.70006 -0.0732232 10.1749 0.21967 10.4678C0.512563 10.7607 0.987437 10.7607 1.28033 10.4678L0.21967 9.40717ZM10.6875 0.75C10.6875 0.335786 10.3517 2.97145e-09 9.9375 1.50485e-07L3.1875 -2.70983e-07C2.77329 -2.70983e-07 2.4375 0.335786 2.4375 0.75C2.4375 1.16421 2.77329 1.5 3.1875 1.5H9.1875V7.5C9.1875 7.91421 9.52329 8.25 9.9375 8.25C10.3517 8.25 10.6875 7.91421 10.6875 7.5L10.6875 0.75ZM0.75 9.9375L1.28033 10.4678L10.4678 1.28033L9.9375 0.75L9.40717 0.21967L0.21967 9.40717L0.75 9.9375Z" fill="currentColor" />
                                       </svg>
                                    </i>
                                 </a>
                              </div>
                              <img class="ca-cta-shape-2 d-none d-sm-inline-block" src="/cunnet/assets/img/cta/shape-3.png" alt="">
                           </div>
                        </div>
                        <div class="col-lg-5">
                           <div class="ca-cta-thumb ml-100">
                              <img src="/cunnet/assets/img/cta/shape-2.png" alt="">
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- ca-cta-area-end -->

         </main>
      
         <footer>

            <!-- footer area start -->
            <div class="tp-footer-area pt-130 pb-70">
               <div class="container">
                  <div class="row">
                     <div class="col-12">
                        <div class="ca-footer-bigtitle-wrap text-center mb-80">
                           <h2 class="tp-hero-title text-scale-anim tp-ff-sequel-bold-head">Trámites Puerto Boyacá</h2>
                        </div>
                     </div>
                     <div class="col-xl-6 col-lg-6">
                        <div class="tp-footer-widget mb-30 tp_fade_anim" data-delay=".3">
                           <h2 class="ca-section-title mb-20 lh-1">Gestiona tus<br> trámites en<br> línea</h2>
                           <a class="tp-btn ca-footer-btn tp-ff-inter" href="/admin">
                              <span>
                                 <span class="text-1">Ingresar al sistema</span>
                                 <span class="text-2">Ingresar al sistema</span>
                              </span>
                              <i>
                                 <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0.21967 9.40717C-0.0732232 9.70006 -0.0732232 10.1749 0.21967 10.4678C0.512563 10.7607 0.987437 10.7607 1.28033 10.4678L0.21967 9.40717ZM10.6875 0.75C10.6875 0.335786 10.3517 2.97145e-09 9.9375 1.50485e-07L3.1875 -2.70983e-07C2.77329 -2.70983e-07 2.4375 0.335786 2.4375 0.75C2.4375 1.16421 2.77329 1.5 3.1875 1.5H9.1875V7.5C9.1875 7.91421 9.52329 8.25 9.9375 8.25C10.3517 8.25 10.6875 7.91421 10.6875 7.5L10.6875 0.75ZM0.75 9.9375L1.28033 10.4678L10.4678 1.28033L9.9375 0.75L9.40717 0.21967L0.21967 9.40717L0.75 9.9375Z" fill="currentColor" />
                                 </svg>
                                 <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0.21967 9.40717C-0.0732232 9.70006 -0.0732232 10.1749 0.21967 10.4678C0.512563 10.7607 0.987437 10.7607 1.28033 10.4678L0.21967 9.40717ZM10.6875 0.75C10.6875 0.335786 10.3517 2.97145e-09 9.9375 1.50485e-07L3.1875 -2.70983e-07C2.77329 -2.70983e-07 2.4375 0.335786 2.4375 0.75C2.4375 1.16421 2.77329 1.5 3.1875 1.5H9.1875V7.5C9.1875 7.91421 9.52329 8.25 9.9375 8.25C10.3517 8.25 10.6875 7.91421 10.6875 7.5L10.6875 0.75ZM0.75 9.9375L1.28033 10.4678L10.4678 1.28033L9.9375 0.75L9.40717 0.21967L0.21967 9.40717L0.75 9.9375Z" fill="currentColor" />
                                 </svg>
                              </i>
                           </a>
                        </div>
                     </div>
                     <div class="col-xxl-3 col-xl-4 col-lg-6">
                        <div class="tp-footer-widget tp-footer-link ca-footer-link mb-30 tp_fade_anim" data-delay=".4">
                           <h5 class="tp-footer-subtitle ca-footer-subtitle tp-ff-inter mb-25">Enlaces rápidos</h5>
                           <div class="tp-hero-social">
                              <a href="https://www.puertoboyaca-boyaca.gov.co" target="_blank" rel="noopener">Afiliaciones ARL</a>
                              <a href="https://www.puertoboyaca-boyaca.gov.co" target="_blank" rel="noopener">Actas de Necesidad</a>
                              <a href="https://www.puertoboyaca-boyaca.gov.co" target="_blank" rel="noopener">Contratación</a>
                              <a href="https://www.puertoboyaca-boyaca.gov.co" target="_blank" rel="noopener">Plan de Adquisiciones</a>
                              <a href="https://www.puertoboyaca-boyaca.gov.co" target="_blank" rel="noopener">Solicitudes BPIM</a>
                              <a href="https://www.puertoboyaca-boyaca.gov.co" target="_blank" rel="noopener">Reportes</a>
                              <a href="https://www.puertoboyaca-boyaca.gov.co" target="_blank" rel="noopener">Ingresar</a>
                           </div>
                        </div>
                     </div>
                     <div class="col-xxl-3 col-xl-5 col-lg-5 col-md-6 col-sm-8">
                        <div class="tp-footer-widget ca-footer-social-item-wrap d-flex justify-content-between ml-45 mr-40 mb-30">
                           <div class="ca-footer-social-item tp_fade_anim" data-delay=".5">
                              <h5 class="tp-footer-subtitle ca-footer-subtitle tp-ff-inter mb-15">Social</h5>
                              <ul>
                                 <li><a href="https://www.facebook.com/share/1EmTDcxiH8/" target="_blank" rel="noopener">Facebook</a></li>
                                 <li><a href="https://www.instagram.com/alcaldiadepuertoboyaca" target="_blank" rel="noopener">Instagram</a></li>
                                 <li><a href="https://www.tiktok.com/@alcaldiadepuertoboyaca" target="_blank" rel="noopener">TikTok</a></li>
                                 <li><a href="https://twitter.com/alcaldiaptoboy/" target="_blank" rel="noopener">X (Twitter)</a></li>
                                 <li><a href="https://www.youtube.com/channel/UCzCGO8srqG8ojZXguEnfvAQ" target="_blank" rel="noopener">YouTube</a></li>
                              </ul>
                           </div>
                           <div class="ca-footer-social-item tp_fade_anim" data-delay=".6">
                              <h5 class="tp-footer-subtitle ca-footer-subtitle tp-ff-inter mb-15">Enlaces</h5>
                              <ul>
                                 <li><a href="https://www.puertoboyaca-boyaca.gov.co" target="_blank" rel="noopener">Sitio web oficial</a></li>
                                 <li><a href="https://www.puertoboyaca-boyaca.gov.co/NuestraAlcaldia/SaladePrensa" target="_blank" rel="noopener">Sala de prensa</a></li>
                                 <li><a href="https://www.puertoboyaca-boyaca.gov.co/Paginas/Politicas-de-Privacidad-y-Condiciones-de-Uso.aspx" target="_blank" rel="noopener">Políticas</a></li>
                                 <li><a href="/admin">Ingresar al sistema</a></li>
                                 
                              </ul>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- footer area end -->

            <!-- tp-footer-copyright-area-start -->
            <div class="tp-footer-copyright-area">
               <div class="tp-about-border pt-25 pb-5">
                  <div class="container">
                     <div class="row align-items-center">
                        <div class="col-lg-6">
                           <div class="tp-footer-copyright-wrap mb-20">
                              <span class="tp-footer-copyright ca-footer-copyright">©<span class="update-year"></span> Alcaldía de Puerto Boyacá.</span>
                           </div>
                        </div>
                        <div class="col-lg-6">
                           <div class="tp-footer-copyright-wrap text-lg-end mb-20">
                              <span class="tp-footer-copyright ca-footer-copyright"><a href="https://www.puertoboyaca-boyaca.gov.co/Paginas/Politicas-de-Privacidad-y-Condiciones-de-Uso.aspx" target="_blank" rel="noopener">Términos y Condiciones</a></span>
                              <span class="tp-footer-copyright ca-footer-copyright ml-140"><a href="https://www.puertoboyaca-boyaca.gov.co/Paginas/Politicas-de-Privacidad-y-Condiciones-de-Uso.aspx" target="_blank" rel="noopener">Política de Privacidad</a></span>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <!-- tp-footer-copyright-area-end -->

         </footer>
      </div>
   </div>


   <!-- JS here -->
   <script src="/cunnet/assets/js/vendor/jquery.js"></script>
   <script src="/cunnet/assets/js/bootstrap.min.js"></script>
   <script src="/cunnet/assets/js/plugin.js"></script>
   <script src="/cunnet/assets/js/split-type.js"></script>
   <script src="/cunnet/assets/js/three.js"></script>
   <script src="/cunnet/assets/js/hover-effect.umd.js"></script>
   <script src="/cunnet/assets/js/swiper-bundle.js"></script>
   <script src="/cunnet/assets/js/magnific-popup.js"></script>
   <script src="/cunnet/assets/js/nice-select.js"></script>
   <script src="/cunnet/assets/js/purecounter.js"></script>
   <script src="/cunnet/assets/js/ajax-form.js"></script>
   <script src="/cunnet/assets/js/animated-headline.js"></script>
   <script src="/cunnet/assets/js/slider-init.js"></script>
   <script src="/cunnet/assets/js/main.js"></script>
   <script src="/cunnet/assets/js/tp-cursor.js"></script>

</body>

</html>
