# 🚀 CMS Kurulum Rehberi

## Basit Kurulum

WordPress tarzı otomatik kurulum sistemi ile kurulum çok kolay!

### Adımlar:

1. **Tarayıcıda açın:** `https://siteniz.com/install.php`

2. **Adım 1:** Veritabanı bilgilerinizi girin
   - Veritabanı sunucusu (genellikle `localhost`)
   - Veritabanı adı
   - Veritabanı kullanıcı adı
   - Veritabanı şifresi

3. **Adım 2:** Site ve admin bilgilerinizi girin
   - Site adı
   - Yönetici kullanıcı adı
   - Yönetici e-posta
   - Yönetici şifresi

4. **Sistem otomatik olarak:**
   - ✅ Config dosyasını oluşturur
   - ✅ Tüm veritabanı tablolarını oluşturur (complete_schema.sql ile)
   - ✅ Admin kullanıcısını oluşturur
   - ✅ Varsayılan ayarları ekler

5. **Kurulum tamamlandı!** Admin paneline giriş yapabilirsiniz.

## Hızlı Kurulum (Sadece Veritabanı)

Eğer config dosyanız varsa ve sadece veritabanı tablolarını yeniden oluşturmak istiyorsanız:

1. **Tarayıcıda açın:** `https://siteniz.com/install/quick_install.php`
2. Script tüm tabloları otomatik olarak oluşturacaktır
3. ✅ Mevcut tablolar korunur (IF NOT EXISTS kullanılır)

**Ne zaman kullanılır?**
- Veritabanı yanlışlıkla silindiyse
- Yeni tablolar eklenmişse (güncellemeler)
- Migration yapmak istiyorsanız


## Dosya Yapısı

```
install/
├── install.php              # Ana kurulum giriş sayfası (root'ta)
├── step1.php                # Adım 1: Veritabanı bilgileri
├── step2.php                # Adım 2: Site ve admin bilgileri
├── install_process.php      # Kurulum işlem sayfası
├── install_process_action.php  # Kurulum işlemleri (config, tablolar, admin)
├── step3.php                # Kurulum tamamlandı sayfası
├── complete_schema.sql      # ✅ KOMPLE VERITABANSI ŞEMASI (TÜM TABLOLAR)
├── schema.sql               # Temel tablolar (users, options, posts, media)
├── posts_schema.sql         # Blog yazıları tabloları
├── post_versions_schema.sql # Yazı versiyonları
├── sliders_schema.sql       # Slider tabloları
├── slider_layers_schema.sql # Slider layer tabloları
├── menus_schema.sql         # Menü tabloları
├── forms_schema.sql         # Form tabloları
├── analytics_schema.sql     # Analitik tabloları
├── agreements_schema.sql    # Sözleşme tabloları
├── themes_schema.sql        # Tema sistemi tabloları (page_sections dahil!)
├── pages_schema.sql         # Sayfa meta tabloları
├── media_schema.sql         # Medya kütüphanesi
├── modules_schema.sql       # Modül sistemi
└── roles_schema.sql         # Rol ve yetki tabloları
```

**ÖNEMLİ NOT:** Kurulum artık `complete_schema.sql` dosyasını kullanır. Bu dosya tüm tabloları içerir ve tekrar çakışmaları önler.


## Sorun Giderme

**"Veritabanı bağlantı hatası" alıyorsanız:**
- cPanel'de veritabanı oluşturduğunuzdan emin olun
- Kullanıcı adı ve şifresinin doğru olduğundan emin olun
- Veritabanı kullanıcısının veritabanına tam erişimi olduğundan emin olun

**"Table doesn't exist" hatası alıyorsanız:**
- Kurulum scriptini tekrar çalıştırın: `/install.php`
- Veya hızlı kurulumu kullanın: `/install/quick_install.php`
- Veritabanı izinlerini kontrol edin (CREATE, ALTER, DROP yetkileri olmalı)
- phpMyAdmin'den `install/check_tables.sql` dosyasını çalıştırıp hangi tabloların eksik olduğunu görün

**Eksik tablolar için:**
1. phpMyAdmin veya başka bir SQL aracıyla veritabanınıza bağlanın
2. `install/complete_schema.sql` dosyasını çalıştırın
3. Veya tarayıcıda `/install/quick_install.php` adresine gidin

**Hangi tablolar eksik diye merak ediyorsanız:**
1. phpMyAdmin'e gidin
2. SQL sekmesine gidin
3. `install/check_tables.sql` dosyasının içeriğini yapıştırın ve çalıştırın
4. Hangi tabloların var olup olmadığını görebilirsiniz


## Güvenlik

✅ Kurulum tamamlandıktan sonra ilk girişten şifrenizi değiştirin!
✅ Production ortamında `display_errors`'ı kapatın
✅ `install.php` ve `install/` klasörünü silmeyi düşünebilirsiniz (opsiyonel)
