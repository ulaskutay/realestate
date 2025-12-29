# SEO Modülü

CMS için kapsamlı SEO yönetim modülü.

## Özellikler

### 1. Sitemap XML
- Otomatik sitemap.xml oluşturma
- Blog yazıları, kategoriler ve etiketler dahil
- Manuel URL ekleme desteği
- Changefreq ve priority ayarları

### 2. Robots.txt Yönetimi
- Admin panelinden düzenleme
- Dinamik robots.txt sunumu
- Sitemap URL'si otomatik eklenir

### 3. Meta Tag Şablonları
- Anasayfa, blog, kategori için title şablonları
- Değişken desteği: `{site_name}`, `{post_title}`, `{category_name}`
- Varsayılan meta description ayarı

### 4. URL Yönlendirmeleri
- 301 (Kalıcı) ve 302 (Geçici) yönlendirme
- Hit sayacı ile kullanım takibi
- CSV içe/dışa aktarma

### 5. Schema.org (JSON-LD)
- Organization şeması
- WebSite şeması (arama kutusu dahil)
- Article şeması (blog yazıları için)

## Kurulum

1. Modülü `modules/seo` klasörüne kopyalayın
2. Admin panelinden modülü aktif edin
3. SEO menüsünden ayarları yapılandırın

## Kullanım

### Sitemap
Sitemap otomatik olarak `/sitemap.xml` adresinde sunulur.

### Robots.txt
Robots.txt `/robots.txt` adresinde dinamik olarak sunulur.

### Meta Taglar
Meta taglar otomatik olarak `<head>` bölümüne eklenir.

### Yönlendirmeler
Yönlendirmeler sayfa yüklenmeden önce otomatik kontrol edilir.

## Lisans

MIT License

