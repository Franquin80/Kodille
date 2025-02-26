# Kodille - Paikallisten palveluiden hakualusta Suomessa

Kodille on suomalainen WordPress-pohjainen palvelu, joka yhdistää asiakkaita ja paikallisia palveluntarjoajia (esim. maalarit, rännien puhdistajat) arvostelupohjaisella alustalla. Projekti hyödyntää CPT UI:ta, ACF:ää ja tulevaisuudessa Google Maps API:ta reaaliaikaiseen dataan.

## Projektin kuvaus
- **Tavoite**: Tarjota helppo tapa löytää luotettavia palveluntarjoajia paikkakunnittain, keskittyen aluksi matalan kilpailun avainsanoihin (esim. "rännien puhdistus Helsinki").
- **Ominaisuudet**:
  - Näytä 5 parasta palveluntarjoajaa per palvelu/paikkakunta Google-arvosteluilla.
  - Mainospaikat listan kärjessä (aluksi omistajan yritykset).
  - SEO-ystävälliset URLit ja orgaaninen liikenne.
- **Teknologia**: WordPress, CPT UI, ACF, (tulossa: Google Maps API).

## Asennus ja käyttö
### Vaatimukset
- WordPress (5.0+)
- Asennetut lisäosat: CPT UI, Advanced Custom Fields (ACF)
- Google Cloud -tili (Places API -avainta varten)
- Paikallinen kehitysympäristö (esim. LocalWP) tai palvelin

/Kodille
├── /wp-content
│   ├── /themes
│   │   ├── /Kodille
│   │   │   ├── functions.php       # Teeman asetukset ja tuleva API-integraatio
│   │   │   ├── single-[cpt].php    # CPT-näyttötemplate (esim. palveluntarjoajat)
│   │   │   ├── style.css           # Teeman tyylit
│   │   │   └── index.php           # Pääsivun template
├── README.md                       # Tämä tiedosto

Nykyinen tila

    ✅ WordPress-teema toiminnassa: Perusrakenne valmis.
    ✅ CPT UI ja ACF määritelty: Palveluntarjoajat ja kentät olemassa (paikallisesti).
    ✅ Reaaliaikainen sivu: Toimii manuaalisella datalla.
    ☐ Google Maps API: Integraatio puuttuu.
    ☐ Mainospaikat: Logiikka puuttuu frontendistä.
    ☐ SEO-alasivut: Suunnitelma olemassa, ei vielä toteutettu.
