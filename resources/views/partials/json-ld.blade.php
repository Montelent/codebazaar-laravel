@php
  use App\Models\SiteSetting;
  use App\Support\Seo;

  $schemaCfg = SiteSetting::getValue('schema', [
      'organization_enabled' => true,
      'organization_name' => Seo::siteName(),
      'organization_url' => url('/'),
      'organization_logo' => '',
      'organization_same_as' => [],
      'website_enabled' => true,
      'product_enabled' => true,
      'breadcrumb_enabled' => true,
      'custom_json_ld' => '',
  ]);

  $blocks = [];

  if (! empty($schemaCfg['organization_enabled'])) {
      $org = [
          '@context' => 'https://schema.org',
          '@type' => 'Organization',
          'name' => $schemaCfg['organization_name'] ?: Seo::siteName(),
          'url' => $schemaCfg['organization_url'] ?: url('/'),
      ];
      if (! empty($schemaCfg['organization_logo'])) {
          $org['logo'] = $schemaCfg['organization_logo'];
      }
      if (! empty($schemaCfg['organization_same_as']) && is_array($schemaCfg['organization_same_as'])) {
          $org['sameAs'] = array_values(array_filter($schemaCfg['organization_same_as']));
      }
      $blocks[] = $org;
  }

  if (! empty($schemaCfg['website_enabled'])) {
      $blocks[] = [
          '@context' => 'https://schema.org',
          '@type' => 'WebSite',
          'name' => Seo::siteName(),
          'url' => url('/'),
          'potentialAction' => [
              '@type' => 'SearchAction',
              'target' => url('/search').'?q={search_term_string}',
              'query-input' => 'required name=search_term_string',
          ],
      ];
  }

  // Page-specific: product
  if (! empty($schemaCfg['product_enabled']) && isset($item) && $item) {
      $price = method_exists($item, 'effectiveRegularPrice') ? $item->effectiveRegularPrice() : (float) ($item->regular_price ?? 0);
      $product = [
          '@context' => 'https://schema.org',
          '@type' => 'Product',
          'name' => $item->title,
          'description' => \Illuminate\Support\Str::limit(strip_tags((string) ($item->seo_description ?: $item->description)), 300),
          'url' => route('item.show', [$item->slug, $item->id]),
          'image' => array_values(array_filter([
              $item->og_image ?: null,
              $item->thumbnail_url ?: null,
          ])),
          'sku' => (string) $item->id,
      ];
      if ($item->author) {
          $product['brand'] = [
              '@type' => 'Brand',
              'name' => $item->author->name ?: $item->author->username,
          ];
      }
      $product['offers'] = [
          '@type' => 'Offer',
          'url' => route('item.show', [$item->slug, $item->id]),
          'priceCurrency' => 'USD',
          'price' => number_format($price, 2, '.', ''),
          'availability' => 'https://schema.org/InStock',
      ];
      if ($item->rating_count > 0) {
          $product['aggregateRating'] = [
              '@type' => 'AggregateRating',
              'ratingValue' => (string) $item->rating_avg,
              'reviewCount' => (string) $item->rating_count,
          ];
      }
      $blocks[] = $product;
  }

  // Breadcrumbs
  if (! empty($schemaCfg['breadcrumb_enabled']) && ! empty($breadcrumbs) && is_array($breadcrumbs)) {
      $elements = [];
      foreach (array_values($breadcrumbs) as $i => $crumb) {
          $elements[] = [
              '@type' => 'ListItem',
              'position' => $i + 1,
              'name' => $crumb['name'] ?? '',
              'item' => $crumb['url'] ?? url('/'),
          ];
      }
      if ($elements) {
          $blocks[] = [
              '@context' => 'https://schema.org',
              '@type' => 'BreadcrumbList',
              'itemListElement' => $elements,
          ];
      }
  }

  $custom = trim((string) ($schemaCfg['custom_json_ld'] ?? ''));
@endphp
@foreach($blocks as $block)
<script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach
@if($custom !== '')
<script type="application/ld+json">{!! $custom !!}</script>
@endif
