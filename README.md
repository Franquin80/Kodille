# Kodille – Paikallisten palveluiden hakualusta Suomessa

**Kodille.com** on suomalainen WordPress-pohjainen palvelualusta, joka yhdistää asiakkaat ja paikalliset palveluntarjoajat (esim. maalarit, rännien puhdistajat).  
Projekti hyödyntää **CPT UI**, **Advanced Custom Fields (ACF)** ja **Google Maps API** -rajapintaa reaaliaikaiseen dataan ja paikalliseen näkyvyyteen.  
Tavoitteena on kasvattaa orgaanista liikennettä SEO-optimoiduilla palvelu- ja hintasivuilla.

---

## 🎯 Tavoite

Tarjota helppo tapa löytää luotettavia palveluntarjoajia paikkakunnittain.  
Painopiste on matalan kilpailun avainsanoissa kuten  
**“rännien puhdistus hinta Oulu”** ja **“katon maalaus hinta Helsinki”**.

---

## 🚀 Ominaisuudet

- **SEO-oppaat:**  
  30 palvelu-hinta-opasta (esim. `/opas/rannien-puhdistus-hinta/oulu`).
- **Palveluntarjoajat:**  
  Top-5 tarjoajaa näytetään jokaisella paikkakuntasivulla.  
  Haku perustuu taksonomioihin `sijainnit` ja `tarjotut_palvelut`.
- **Hakulomake:**  
  Käyttäjä valitsee Maakunta → Paikkakunta → Palvelukategoria → Palvelu.  
  `palvelu.paikkakunnat.json` toimii autocomplete-lähteenä.
- **Mainospaikat:**  
  ACF-kentät `sponsoroidut_alueet` yritysten lisänäkyvyyteen.
- **Google Places API:**  
  Reaaliaikaiset arvostelut ja yhteystiedot (integraatio valmis, avain vielä lisättävä).

---

## ⚙️ Teknologia

- **WordPress 6.7+**
- **Teema:** Astra Child  
- **Lisäosat:**
  - Custom Post Type UI  
  - Advanced Custom Fields Pro  
  - Rank Math SEO  
  - WP Sheet Editor  
  - Limit Login Attempts Reloaded
- **Google Places / Maps API**
- **JSON-datat:**  
  - `palvelu.paikkakunnat.json` (kunnat autocompletea varten)

---

## 📁 Tiedostorakenne
/wp-content/themes/astra-child/
│
├── functions.php # Hookit ja API-logiikka
├── style.css # Tyylit
│
├── /includes/
│ ├── google-places-helpers.php # Google Places -apufunktiot
│ ├── palveluntarjoajahaku.php # Admin-haku ja shortcode
│ ├── admin-import.php # Tuontityökalu (vain admin)
│
├── /acf/
│ ├── palvelun tiedot.json
│ ├── palveluntarjoajan tiedot.json
│ ├── sijainnin tiedot.json
│
├── /templates/
│ ├── single-palveluntarjoajat.php
│ ├── single-palvelut.php
│ ├── archive-palveluntarjoajat.php
│ ├── page-haku.php # Hakusivu
│
├── /js/
│ └── custom.js # Hakulomakkeen logiikka
│
├── /data/
│ └── palvelu.paikkakunnat.json # Suomen paikkakunnat
│
└── README.md

---

## 🧩 CPT:t ja taksonomiat

| CPT | Käyttötarkoitus | Taksonomiat |
|------|------------------|-------------|
| `palvelut` | Yksittäiset palvelut | `palvelukategoriat`, `sijainnit` |
| `palveluntarjoajat` | Yritykset ja yhteystiedot | `sijainnit`, `tarjotut_palvelut` |
| `sijainnit` | Maakunnat ja kunnat | `sijainnit`, `alueet` |
| `opas` | SEO-sisällöt (palvelu-hinta-oppaat) | – |

---

## 🧠 Nykyinen tila (7.11.2025)

✅ CPT-rakenne valmis  
✅ ACF-kenttäryhmät määritelty (`palvelut`, `palveluntarjoajat`, `sijainnit`)  
✅ Paikkakuntadata täysi (`palvelu.paikkakunnat.json`)  
✅ Shortcode hakee tarjoajat taksonomioiden perusteella  
☑️ Google API -avain lisättävä `wp-config.php`:hen  
☑️ Hakulomake viimeisteltävä (`[kodille_haku]`)  
☑️ Mainospaikkojen logiikka työn alla  

---

## 📅 Seuraavat vaiheet
1. Lisää Google API -avain ja testaa haku (`google-places-helpers.php`).
2. Julkaise hakulomake etusivulle (`page-haku.php` tai `[kodille_haku]`).
3. Lisää 30 SEO-opasta WordPressin kautta.
4. Lisää mainoslogiikka sponsoroiduille alueille.
5. Päivitä Rank Math -sitemap ja varmista CPT-indexointi.

---

## 👨‍💻 Kehittäjä

**Markus Takalo**  
📍 Kodille.com – Paikallisten palveluiden hakualusta Suomessa  
🛠️ WordPress + ACF + CPT UI + Google Maps API


