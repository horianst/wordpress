## Abonarea la API

- Se acceseaza https://urgentcargus.portal.azure-api.net/
- Se apasa butonul `Sign up` si se completeaza formularul (nu se pot folosi credentialele pe care clientul le are pentru UrgentOnline / WebExpress)
- Se confirma inregistrarea prin click pe link-ul primit pe mail (trebuie folosita o adresa de email reala)
- In pagina https://urgentcargus.portal.azure-api.net/developer se da click pe `PRODUCTS` in menu, apoi pe `UrgentOnlineAPI` si se apasa `Subscribe`, apoi `Confirm`
- Dupa ce echipa Cargus confirma subscriptia la API, clientul primeste un email de confirmare
- In pagina https://urgentcargus.portal.azure-api.net/developer se da click pe numele utilizatorului din partea dreapta-sus, apoi se apasa `Profile`
- Cele doua subscription keys sunt mascate de caracterele `xxx...xxx` si se apasa `Show` in dreptul fiecareia pentru afisare
- Se recomanda utilizarea `Primary key` in modulul Cargus

## Instalarea modulului

Se instaleaza ca un modul obisnuit de wordpress, urcati folderul 'urgentcargus' in '/wp-content/plugins/' si se instaleaza din backend -> Module.
Se configureaza din backend -> WooCommerce -> Setari -> Livrare cu Cargus.
Dupa ce se introduc adresa api-ului, cheia, userul si parola vor aparea aceleasi campuri ca si la celelalte module (punct de ridicare, asigurare, etc...).

Modulul creaza automat AWB-uri atunci cand comenzile se trec intr-una din starile 'Se prelucreaza' sau 'Finalizata'.
Similar, AWB-urile se sterg atunci cand comenzile se trec intr-una din starile 'In astepare', 'Anulata', 'Rambursata' sau 'Esuata'.
