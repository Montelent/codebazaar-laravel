<style>
:root{--cc-green:{{ $cPrimary }};--cc-green-hover:{{ $cHover }};--cc-secondary:{{ $cSecondary }};--cc-header-bg:{{ $cHeaderBg }};--cc-footer-bg:{{ $cFooterBg }};--cc-footer-text:{{ $cFooterText }};--cc-announcement-bg:{{ $cAnnBg }};--cc-border:#e5e7eb;--cc-bg:#f5f7fa;--cc-text:#333;--cc-header-h:56px}
*{box-sizing:border-box}body{font-family:Inter,system-ui,sans-serif;background:var(--cc-bg);color:var(--cc-text);margin:0}a{color:inherit}
.cc-container{width:100%;max-width:1200px;margin:0 auto;padding:0 16px}
@media(min-width:640px){.cc-container{padding:0 20px}}
@media(min-width:1024px){.cc-container{padding:0 24px}}
.cc-topbar{background:var(--cc-header-bg);border-bottom:1px solid #e8e8e8}
.cc-topbar-inner{display:flex;align-items:center;gap:16px;min-height:var(--cc-header-h);padding-top:10px;padding-bottom:10px}
.cc-logo{display:inline-flex;align-items:center;gap:8px;text-decoration:none;font-weight:800;font-size:1.15rem;color:#1a1a1a;white-space:nowrap;flex-shrink:0}
.cc-logo-mark{width:28px;height:28px;border-radius:4px;background:var(--cc-green);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:12px}
.cc-header-search{flex:1;max-width:520px;position:relative}
.cc-header-search input{width:100%;height:40px;border:1px solid #d0d0d0;border-radius:4px;padding:0 40px 0 14px;font-size:14px;background:#fff}
.cc-header-search input:focus{outline:none;border-color:var(--cc-green);box-shadow:0 0 0 2px color-mix(in srgb,var(--cc-green) 25%,transparent)}
.cc-header-search button{position:absolute;right:0;top:0;bottom:0;width:42px;border:0;background:transparent;color:#666;cursor:pointer}
.cc-header-actions{display:flex;align-items:center;gap:8px;margin-left:auto;flex-shrink:0}
.cc-header-actions a,.cc-header-actions button{font-size:13px;font-weight:500;color:#444;text-decoration:none;background:none;border:0;cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:6px 8px;border-radius:6px}
.cc-header-actions a:hover,.cc-header-actions button:hover{color:var(--cc-green);background:rgba(0,0,0,.04)}
.cc-header-actions svg{width:18px;height:18px;flex-shrink:0}
.cc-btn-cart{position:relative;border:1px solid #ddd!important;border-radius:6px!important;padding:7px 12px!important;font-weight:600!important}
.cc-btn-cart:hover{border-color:var(--cc-green)!important}
.cc-cart-badge{position:absolute;top:-6px;right:-6px;min-width:18px;height:18px;padding:0 5px;border-radius:999px;background:var(--cc-green);color:#fff;font-size:10px;font-weight:700;line-height:18px;text-align:center}
.cc-btn-green{background:var(--cc-green)!important;color:#fff!important;border-radius:6px;padding:8px 14px!important;font-weight:600!important;font-size:13px!important;text-decoration:none;gap:6px}
.cc-btn-green:hover{background:var(--cc-green-hover)!important;color:#fff!important}
.cc-btn-green svg{stroke:#fff}
@media(max-width:640px){.cc-icon-label{display:none}.cc-btn-cart{padding:8px!important}.cc-btn-green{padding:8px 10px!important}}
.cc-subnav{background:var(--cc-header-bg);border-bottom:1px solid #e8e8e8}
.cc-subnav-inner{display:flex;align-items:center;gap:4px;overflow-x:auto;min-height:42px;scrollbar-width:none}
.cc-subnav-inner::-webkit-scrollbar{display:none}
.cc-subnav a{flex-shrink:0;padding:10px 12px;font-size:13px;font-weight:500;color:#555;text-decoration:none;white-space:nowrap}
.cc-subnav a:hover{color:var(--cc-green)}
.cc-menu-btn{display:none;width:40px;height:40px;border:1px solid #e5e7eb;border-radius:4px;background:#fff;align-items:center;justify-content:center;cursor:pointer}
@media(max-width:767px){.cc-header-search{order:3;max-width:none;width:100%}.cc-topbar-inner{flex-wrap:wrap}.cc-menu-btn{display:inline-flex}.cc-hide-mobile{display:none!important}}
.cc-drawer{position:fixed;inset:0;z-index:60;display:none}.cc-drawer.is-open{display:block}
.cc-drawer-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.45)}
.cc-drawer-panel{position:absolute;top:0;right:0;bottom:0;width:min(300px,88vw);background:#fff;box-shadow:-8px 0 24px rgba(0,0,0,.12);padding:16px;overflow-y:auto}
.cc-drawer-panel a{display:flex;align-items:center;gap:10px;padding:12px 8px;color:#1e293b;text-decoration:none;font-weight:500;border-bottom:1px solid #f1f5f9;font-size:14px}
.cc-drawer-panel a svg{width:18px;height:18px;flex-shrink:0;color:#64748b}
.cc-footer{background:var(--cc-footer-bg);color:var(--cc-footer-text);margin-top:56px}
.cc-footer a{color:inherit;text-decoration:none}.cc-footer a:hover{color:#fff}
.cc-hero{background:var(--cc-secondary)}
.text-brand,a.text-brand{color:var(--cc-green)!important}.bg-brand{background-color:var(--cc-green)!important}
.bg-brand:hover,.hover\:bg-brand:hover{background-color:var(--cc-green-hover)!important}
.border-brand{border-color:var(--cc-green)!important}
main.cc-main{padding:24px 0 48px;min-height:50vh}
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
.item-body strong,.item-body b{font-weight:700}
.item-body img{max-width:100%;height:auto;border-radius:4px;margin:1em 0}
.item-body blockquote{border-left:3px solid var(--cc-green);margin:1em 0;padding:.5em 1em;color:#555;background:#f8faf8}
.item-body table{width:100%;border-collapse:collapse;margin:1em 0;font-size:14px}
.item-body th,.item-body td{border:1px solid #e5e7eb;padding:8px 10px;text-align:left}
.item-body th{background:#f5f5f5;font-weight:600}
.item-body pre{background:#1e1e1e;color:#f1f1f1;padding:12px 14px;border-radius:6px;overflow-x:auto;margin:1em 0;font-family:ui-monospace,monospace;font-size:13px}
.item-body code{background:#f3f4f6;padding:1px 5px;border-radius:3px;font-family:ui-monospace,monospace;font-size:13px}
.item-body pre code{background:transparent;padding:0;color:inherit}
.cc-ad-slot{text-align:center;overflow:hidden}
.text-\[\#82b440\],.hover\:text-\[\#82b440\]:hover{color:var(--cc-green)!important}
.bg-\[\#82b440\],.hover\:bg-\[\#82b440\]:hover{background-color:var(--cc-green)!important}
.hover\:bg-\[\#6f9a36\]:hover{background-color:var(--cc-green-hover)!important}
.border-\[\#82b440\],.hover\:border-\[\#82b440\]:hover{border-color:var(--cc-green)!important}
.bg-\[\#1b2838\]{background-color:var(--cc-secondary)!important}
.focus\:ring-\[\#82b440\]:focus{--tw-ring-color:var(--cc-green)!important}
</style>
