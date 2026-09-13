@php
    $theme = \App\Models\SiteSetting::getValue('colors', [
        'primary' => '#e11d2e',
        'primary_hover' => '#c1121f',
        'secondary' => '#0b1220',
        'header_bg' => '#ffffff',
        'footer_bg' => '#0b1220',
        'footer_text' => '#94a3b8',
        'announcement_bg' => '#0b1220',
    ]);
    $cPrimary = $theme['primary'] ?? '#e11d2e';
    $cHover = $theme['primary_hover'] ?? '#c1121f';
    $cSecondary = $theme['secondary'] ?? '#0b1220';
    $cHeaderBg = $theme['header_bg'] ?? '#ffffff';
    $cFooterBg = $theme['footer_bg'] ?? '#0b1220';
    $cFooterText = $theme['footer_text'] ?? '#94a3b8';
    $cAnnBg = $theme['announcement_bg'] ?? '#0b1220';
@endphp
<style>
:root{--cc-green:{{ $cPrimary }};--cc-green-hover:{{ $cHover }};--cc-secondary:{{ $cSecondary }};--cc-header-bg:{{ $cHeaderBg }};--cc-footer-bg:{{ $cFooterBg }};--cc-footer-text:{{ $cFooterText }};--cc-announcement-bg:{{ $cAnnBg }};--cc-border:#e5e7eb;--cc-bg:#f5f7fa;--cc-text:#0f172a;--cc-header-h:56px;--cc-accent:{{ $cPrimary }}}
*{box-sizing:border-box}body{font-family:Inter,system-ui,sans-serif;background:var(--cc-bg);color:var(--cc-text);margin:0}a{color:inherit}
.cc-container{width:100%;max-width:1120px;margin:0 auto;padding:0 16px}
@media(min-width:640px){.cc-container{padding:0 20px}}
@media(min-width:1024px){.cc-container{padding:0 24px}}

/* Header v2 */
.cc-header-v2{background:var(--cc-header-bg);border-bottom:1px solid #e8e8e8}
.cc-util-bar{border-bottom:1px solid #eef0f3;background:#fafbfc}
.cc-util-inner{display:flex;align-items:center;justify-content:space-between;min-height:36px;gap:12px}
.cc-util-left,.cc-util-right{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
.cc-util-bar a{font-size:13px;color:#64748b;text-decoration:none;font-weight:500}
.cc-util-bar a:hover{color:#0f172a}
.cc-util-cta{color:#0d9488!important;font-weight:600!important}
.cc-main-inner{display:flex;align-items:center;justify-content:space-between;min-height:60px;gap:12px}
.cc-main-left{display:flex;align-items:center;gap:10px}
.cc-main-right{display:flex;align-items:center;gap:10px}
.cc-logo-v2{display:inline-flex;align-items:center;gap:10px;text-decoration:none;color:#0f172a}
.cc-logo-cube{width:36px;height:36px;border-radius:10px;background:linear-gradient(145deg,#10b981,#059669);color:#fff;display:inline-flex;align-items:center;justify-content:center}
.cc-logo-text{font-weight:800;font-size:1.25rem;letter-spacing:-0.02em}
.cc-icon-btn,.cc-account-btn{position:relative;display:inline-flex;align-items:center;justify-content:center;gap:4px;width:42px;height:42px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;color:#334155;text-decoration:none;cursor:pointer;font:inherit}
.cc-account-btn{width:auto;padding:0 12px;border-radius:999px}
.cc-icon-btn:hover,.cc-account-btn:hover,.cc-account-wrap.is-open .cc-account-btn{border-color:#cbd5e1;background:#f8fafc}
.cc-account-wrap.is-open .cc-account-chevron{transform:rotate(180deg)}
.cc-account-chevron{transition:transform .15s ease}
.cc-cart-badge{position:absolute;top:-5px;right:-5px;min-width:18px;height:18px;padding:0 5px;border-radius:999px;background:var(--cc-accent);color:#fff;font-size:10px;font-weight:700;line-height:18px;text-align:center}
.cc-menu-btn{display:inline-flex;width:40px;height:40px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;align-items:center;justify-content:center;cursor:pointer;color:#334155}
.cc-subnav-v2{border-top:1px solid #eef0f3;background:#fff}
.cc-subnav-inner{display:flex;align-items:center;gap:4px;overflow-x:auto;min-height:42px;scrollbar-width:none}
.cc-subnav-inner::-webkit-scrollbar{display:none}
.cc-subnav-v2 a{flex-shrink:0;padding:10px 12px;font-size:13px;font-weight:500;color:#64748b;text-decoration:none;white-space:nowrap}
.cc-subnav-v2 a:hover{color:var(--cc-accent)}
@media(max-width:640px){.cc-util-bar{display:none}}

/* Account dropdown */
.cc-account-wrap{position:relative}
.cc-account-menu{position:absolute;top:calc(100% + 8px);right:0;z-index:50;width:min(280px,92vw);background:#fff;border:1px solid #e8ecf1;border-radius:16px;box-shadow:0 16px 40px rgba(15,23,42,.14);padding:8px;overflow:hidden}
.cc-account-menu[hidden]{display:none!important}
.cc-account-menu-head{padding:12px 14px 10px;border-bottom:1px solid #f1f5f9;margin-bottom:4px}
.cc-account-menu-name{margin:0;font-size:14px;font-weight:700;color:#0f172a}
.cc-account-menu-email{margin:2px 0 0;font-size:12px;color:#64748b;word-break:break-all}
.cc-account-item{display:flex;align-items:center;gap:12px;width:100%;padding:11px 14px;border:0;border-radius:10px;background:transparent;color:#334155;font-size:14px;font-weight:500;text-decoration:none;text-align:left;cursor:pointer;font-family:inherit}
.cc-account-item svg{flex-shrink:0;color:#64748b}
.cc-account-item:hover{background:#f8fafc;color:#0f172a}
.cc-account-item:hover svg{color:#0f172a}
.cc-account-cta{color:#0d9488!important}
.cc-account-menu-divider{height:1px;background:#f1f5f9;margin:6px 8px}
.cc-account-signout{color:#dc2626!important}
.cc-account-signout svg{color:#dc2626!important}
.cc-account-signout:hover{background:#fef2f2!important}
.cc-account-logout{margin:0}

/* Hero v2 */
.cc-hero-v2{background:var(--cc-secondary);color:#fff;padding:64px 0 72px;text-align:center}
@media(min-width:768px){.cc-hero-v2{padding:88px 0 96px}}
.cc-hero-eyebrow{font-size:15px;font-weight:600;color:var(--cc-accent);margin:0 0 16px}
.cc-hero-title{margin:0;font-weight:800;letter-spacing:-0.03em;line-height:1.1}
.cc-hero-title-main{display:block;font-size:clamp(2rem,5vw,3.25rem);color:#fff}
.cc-hero-title-accent{display:block;font-size:clamp(2rem,5vw,3.25rem);color:var(--cc-accent);margin-top:4px}
.cc-hero-sub{max-width:520px;margin:20px auto 0;font-size:1.05rem;line-height:1.55;color:#cbd5e1}
.cc-hero-search{display:flex;max-width:560px;margin:32px auto 0;border-radius:999px;overflow:hidden;box-shadow:0 12px 40px rgba(0,0,0,.25);background:#fff}
.cc-hero-search-field{flex:1;display:flex;align-items:center;gap:10px;padding:0 18px;background:#fff}
.cc-hero-search-icon{color:#94a3b8;flex-shrink:0}
.cc-hero-search input{flex:1;border:0;outline:none;height:52px;font-size:15px;color:#0f172a;background:transparent}
.cc-hero-search-btn{border:0;background:var(--cc-accent);color:#fff;font-weight:700;font-size:15px;padding:0 28px;cursor:pointer;white-space:nowrap}
.cc-hero-search-btn:hover{background:var(--cc-green-hover)}
@media(max-width:480px){.cc-hero-search{flex-direction:column;border-radius:16px}.cc-hero-search-btn{height:48px;border-radius:0 0 16px 16px}}

/* Browse by category */
.cc-section-title{font-size:1.5rem;font-weight:800;color:#0f172a;margin:0 0 20px;letter-spacing:-0.02em}
.cc-browse-grid{display:grid;gap:14px;grid-template-columns:repeat(1,minmax(0,1fr))}
@media(min-width:520px){.cc-browse-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:900px){.cc-browse-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}}
.cc-browse-card{display:flex;flex-direction:column;align-items:flex-start;gap:6px;padding:22px 20px;background:#fff;border:1px solid #e8ecf1;border-radius:16px;text-decoration:none;box-shadow:0 1px 2px rgba(15,23,42,.04);transition:border-color .15s,box-shadow .15s,transform .15s}
.cc-browse-card:hover{border-color:#cbd5e1;box-shadow:0 8px 24px rgba(15,23,42,.08);transform:translateY(-2px)}
.cc-browse-icon{font-size:28px;line-height:1;margin-bottom:6px;display:inline-flex;align-items:center;justify-content:center;min-height:36px}
.cc-browse-icon img{width:36px;height:36px;object-fit:contain}
.cc-browse-name{font-size:1.05rem;font-weight:700;color:#0f172a}
.cc-browse-desc{font-size:13px;color:#64748b;line-height:1.4}

/* Drawer */
.cc-drawer{position:fixed;inset:0;z-index:60;display:none}.cc-drawer.is-open{display:block}
.cc-drawer-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.45)}
.cc-drawer-panel{position:absolute;top:0;left:0;bottom:0;width:min(300px,88vw);background:#fff;box-shadow:8px 0 24px rgba(0,0,0,.12);padding:16px;overflow-y:auto}
.cc-drawer-panel a{display:flex;align-items:center;gap:10px;padding:12px 8px;color:#1e293b;text-decoration:none;font-weight:500;border-bottom:1px solid #f1f5f9;font-size:14px}

.cc-footer{background:var(--cc-footer-bg);color:var(--cc-footer-text);margin-top:56px}
.cc-footer a{color:inherit;text-decoration:none}.cc-footer a:hover{color:#fff}
main.cc-main{padding:28px 0 56px;min-height:50vh}
.cc-grid{display:grid;gap:16px;grid-template-columns:repeat(1,minmax(0,1fr))}
@media(min-width:480px){.cc-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:768px){.cc-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}}
@media(min-width:1100px){.cc-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
@media(min-width:1024px){.cc-buy-box{position:sticky;top:72px}}
.item-body{font-size:15px;line-height:1.7;color:#333}
.item-body h1,.item-body h2,.item-body h3,.item-body h4{font-weight:700;color:#111;margin:1.25em 0 .5em;line-height:1.3}
.item-body h1{font-size:1.75rem}.item-body h2{font-size:1.4rem}.item-body h3{font-size:1.2rem}
.item-body p{margin:0 0 1em}.item-body ul,.item-body ol{margin:0 0 1em;padding-left:1.5em}
.item-body ul{list-style:disc}.item-body ol{list-style:decimal}
.item-body a{color:var(--cc-green);text-decoration:underline}
.item-body img{max-width:100%;height:auto;border-radius:4px;margin:1em 0}
.item-body pre{background:#1e1e1e;color:#f1f1f1;padding:12px 14px;border-radius:6px;overflow-x:auto;margin:1em 0;font-family:ui-monospace,monospace;font-size:13px}
.cc-ad-slot{text-align:center;overflow:hidden}
.text-brand,a.text-brand{color:var(--cc-green)!important}.bg-brand{background-color:var(--cc-green)!important}
</style>
