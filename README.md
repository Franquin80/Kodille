# Kodille - Paikallisten palveluiden hakualusta Suomessa

Kodille on suomalainen WordPress-pohjainen palvelu, joka yhdistää asiakkaita ja paikallisia palveluntarjoajia (esim. maalarit, rännien puhdistajat) arvostelupohjaisella alustalla. Projekti hyödyntää CPT UI:ta, ACF:ää ja Google Maps API:ta reaaliaikaiseen dataan, keskittyen orgaanisen liikenteen hankintaan SEO-ystävällisillä oppailla.

## Projektin kuvaus

### Tavoite
Tarjota helppo tapa löytää luotettavia palveluntarjoajia paikkakunnittain, keskittyen matalan kilpailun avainsanoihin (esim. "rännien puhdistus hinta Oulu") ja tuoda orgaanista liikennettä oppaiden kautta.

### Ominaisuudet
- **SEO-oppaat**: 30 palvelu-hinta-opasta (esim. `/opas/rannien-puhdistus-hinta/oulu`) orgaanisen liikenteen hankintaan.
- **Palveluntarjoajat**: Näytä 5 parasta paikkakuntakohtaista tarjoajaa Google-arvosteluilla ja `palveluntarjoajat`-CPT:stä.
- **Hakulomake**: Käyttäjä valitsee maakunta → paikkakunta → palvelukategoria → palvelu `paikkakunnat.json`:n avulla.
- **Mainospaikat**: Tulevaisuudessa mainostilaa listan kärjessä yrityksille.

### Teknologia
- WordPress (5.0+)
- CPT UI: `opas`, `palvelut`, `palveluntarjoajat`
- ACF: SEO-kentät ja palvelutiedot
- Google Maps API (tulossa)
- `paikkakunnat.json`: Dynaaminen paikkakuntadata

## Asennus ja käyttö

### Vaatimukset
- WordPress (5.0+)
- Lisäosat: CPT UI, Advanced Custom Fields (ACF)
- Google Cloud -tili (Places API -avain)
- Paikallinen kehitysympäristö (esim. LocalWP) tai palvelin

### Tiedostorakenne
/Kodille
├── /wp-content
│   ├── /themes
│   │   └── /kodille
│   │       ├── functions.php                  # Teeman keskeiset hookit ja API-integraatio
│   │       ├── archive-*.php                  # CPT-arkistot (palvelut, palveluntarjoajat, sijainnit)
│   │       ├── front-page.php                 # Etusivun template
│   │       ├── single-*.php                   # CPT-yksittäiset näkymät
│   │       ├── style.css                      # Teeman tyylit
│   │       ├── paikkakunnat.json              # Suomen paikkakunnat ja maakunnat
│   │       ├── acf-fields.php                 # Vientitiedosto ACF-kentistä
│   │       ├── /includes
│   │       │   ├── google-places-helpers.php  # Google Places -apufunktiot
│   │       │   └── palveluntarjoajahaku.php   # Admin-työkalu Google-hakuihin
│   │       ├── /js
│   │       │   └── custom.js                  # Hakulomakkeen dynaaminen logiikka
│   │       └── /templates
│   │           ├── etusivu.php                # Etusivun HTML-malli
│   │           └── malli.php                  # Esimerkkipohja jatkokehitykselle
├── README.md  # Tämä tiedosto


## Nykyinen tila (27.2.2025)
- ✅ **WordPress-teema toiminnassa**: Perusrakenne valmis.
- ✅ **CPT UI ja ACF määritelty**:  
  - `opas`: 30 palvelu-hinta-opasta (esim. "Rännien puhdistus hinta").  
  - `palvelut`: 60 palvelua (slug: `palvelut`).  
  - `palveluntarjoajat`: Yritykset linkitetty `palvelukategoriat`-taksonomiaan.
- ✅ **Reaaliaikainen sivu**: `single-opas.php` tukee dynaamista sisältöä (`paikkakunnat.json`).  
- ✅ **SEO-ystävälliset URLit**: `/opas/[palvelu-hinta]/[paikkakunta]` (esim. `/opas/rannien-puhdistus-hinta/oulu`).  
- ☐ **Google Maps API**: Avain puuttuu, integraatio kesken `single-opas.php`:ssä.  
- ☐ **Mainospaikat**: Logiikka puuttuu frontendistä.  
- ☐ **Hakulomake**: Suunniteltu, mutta ei vielä lisätty (`index.php` tai erillinen sivu).  
- ☐ **30 palvelu-hinta-opasta**: Suunnitelma valmis, mutta lisäys kesken hallintapaneelissa.

## Kesken olevat asiat
1. **Google Maps API**:  
   - Lisää API-avain `single-opas.php`:hen ja testaa 5 parhaan tarjoajan haku.  
2. **30 palvelu-hinta-opasta**:  
   - Lisää hallintapaneelissa (esim. "Rännien puhdistus hinta", slug: `rannien-puhdistus-hinta`).  
   - Täytä ACF-kentät (SEO, hinta) jokaiselle.  
3. **Hakulomake**:  
   - Lisää `index.php`:hen tai erilliseen sivuun (`page-haku.php`).  
   - Testaa maakunta → paikkakunta → palvelukategoria → palvelu -valinnat `paikkakunnat.json`:lla.  
4. **Palveluntarjoajat**:  
   - Varmista, että `toiminta_alue`-kenttä on täytetty (esim. "Oulu") ja linkitys `palvelukategoriat`-taksonomian kautta toimii.  

## Seuraavat vaiheet
1. **Testaa URL**:  
   - `/opas/rannien-puhdistus-hinta/oulu`.  
   - `/opas/katon-maalaus-hinta/helsinki`.  
   - Varmista taivutus ja sisältö `single-opas.php`:ssä.  
2. **Lisää Google Maps API**: Päivitä `single-opas.php` ja testaa tarjoajalistaus.  
3. **Lisää 30 opasta**: Hallintapaneelissa, täytä ACF-kentät.  
4. **Lisää hakulomake**: Päivitä `index.php` tai luo `page-haku.php`.  
5. **SEO-optimointi**: Tarkista sitemap (esim. Yoast SEO) ja varmista URL:ien indeksointi.

## Huomioita
- **`functions.php`**: Sisältää vain rewrite-säännöt, toiminnallisuus (taivutus, koordinaatit) siirretty `single-opas.php`:hen.  
- **`paikkakunnat.json`**: Tukee dynaamista paikkakuntakäsittelyä – varmista, että `maakunta`-kenttä on mukana (esim. `"maakunta": "Pohjois-Pohjanmaa"`).  
- **Haku**: Hakulomake tukee maakunta-pohjaista navigointia, mutta palvelukategorioiden linkitys palveluihin vaatii vielä tarkennusta (ACF tai taksonomia).  
- **Mainospaikat**: Ei vielä suunniteltu tarkasti – lisättävä frontend-logiikkaan tulevaisuudessa.

