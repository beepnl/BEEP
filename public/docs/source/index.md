---
title: API Reference

language_tabs:
- bash
- javascript

includes:

search: true

toc_footers:
- <a href='http://github.com/mpociot/documentarian'>Documentation Powered by Documentarian</a>
---
<!-- START_INFO -->
# Info

Welcome to the generated API reference.
[Get Postman Collection](http://test.beep.nl/docs/collection.json)

<!-- END_INFO -->

#Api\CategoryController

All categories in the categorization tree used for hive inspections
Only used to get listing (index) or one category (show)
<!-- START_109013899e0bc43247b0f00b67f889cf -->
## api/categories
Display a listing of the inspection categories.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/categories" 
```

```javascript
const url = new URL("https://test.beep.nl/api/categories");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
[
    {
        "id": 1,
        "type": "system",
        "name": "apiary",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "list",
        "trans": {
            "en": "Apiary",
            "nl": "Bijenstand",
            "de": "Bienenstand",
            "fr": "Rucher",
            "ro": "Stupină",
            "pt": "Apiário",
            "es": "Apiario",
            "da": "Bigård"
        },
        "unit": null,
        "children": [
            {
                "id": 2,
                "type": "0",
                "name": "name",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "text",
                "trans": {
                    "en": "Name",
                    "nl": "Naam",
                    "de": "Name",
                    "fr": "Nom",
                    "ro": "Nume",
                    "pt": "Nome",
                    "es": "Nombre",
                    "da": "Navn"
                },
                "unit": null
            },
            {
                "id": 3,
                "type": "list",
                "name": "location",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "select_location",
                "trans": {
                    "en": "Location",
                    "nl": "Locatie",
                    "de": "Ort",
                    "fr": "Lieux",
                    "ro": "Locație",
                    "pt": "Localização",
                    "es": "Ubicación",
                    "da": "Lokation"
                },
                "unit": null
            },
            {
                "id": 12,
                "type": "1",
                "name": "number_of_bee_colonies",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "number_positive",
                "trans": {
                    "en": "Number of bee colonies",
                    "nl": "Aantal bijenvolken",
                    "de": "Anzahl an Bienenvölkern",
                    "fr": "Nombre de colonies",
                    "ro": "Număr de colonii",
                    "pt": "Número de colónias",
                    "es": "Número de colonias de abejas melíferas",
                    "da": "Antal bifamilier"
                },
                "unit": null
            },
            {
                "id": 13,
                "type": "list",
                "name": "orientation",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "options",
                "trans": {
                    "en": "Orientation",
                    "nl": "Orientatie",
                    "de": "Orientierung",
                    "fr": "Orientation",
                    "ro": "Orientare",
                    "pt": "Orientação",
                    "es": "Orientación",
                    "da": "Retning"
                },
                "unit": null
            },
            {
                "id": 25,
                "type": "list",
                "name": "status",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "options",
                "trans": {
                    "en": "Status",
                    "nl": "Status",
                    "de": "Status",
                    "fr": "Statut",
                    "ro": "Stare",
                    "pt": "Estado",
                    "es": "Estado",
                    "da": "Status"
                },
                "unit": null
            },
            {
                "id": 28,
                "type": "system",
                "name": "photo",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "image",
                "trans": {
                    "en": "Photo",
                    "nl": "Foto",
                    "de": "Foto",
                    "fr": "Photo",
                    "ro": "Poză",
                    "pt": "Fotografia",
                    "es": "Foto",
                    "da": "Foto"
                },
                "unit": null
            },
            {
                "id": 913,
                "type": null,
                "name": "can_be_removed",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "list",
                "trans": {
                    "de": "kann entfernt werden",
                    "ro": "poate fi înlăturat",
                    "es": "Puede ser removido"
                },
                "unit": null
            },
            {
                "id": 932,
                "type": "checklist",
                "name": "type",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "select",
                "trans": {
                    "en": "Type",
                    "nl": "Type",
                    "de": "Typ",
                    "fr": "type",
                    "ro": "Tip",
                    "pt": "Tipo",
                    "es": "Tipo",
                    "da": "Type"
                },
                "unit": null
            }
        ]
    },
    {
        "id": 29,
        "type": "system",
        "name": "hive",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "list",
        "trans": {
            "en": "Hive",
            "nl": "Kast",
            "de": "Beute",
            "fr": "Ruche",
            "ro": "Stup",
            "pt": "Colmeia",
            "es": "Colmena",
            "da": "Stade"
        },
        "unit": null,
        "children": [
            {
                "id": 30,
                "type": "system",
                "name": "id",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "ID",
                    "nl": "ID",
                    "de": "ID",
                    "fr": "ID",
                    "ro": "ID",
                    "pt": "ID",
                    "es": "ID",
                    "da": "ID"
                },
                "unit": null
            },
            {
                "id": 34,
                "type": "system",
                "name": "type",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Type",
                    "nl": "Type",
                    "de": "Typ",
                    "fr": "type",
                    "ro": "Tip",
                    "pt": "Tipo",
                    "es": "Tipo",
                    "da": "Type"
                },
                "unit": null
            },
            {
                "id": 64,
                "type": "system",
                "name": "frame_size",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Frame size",
                    "nl": "Raam afmetingen",
                    "de": "Rähmchengröße",
                    "fr": "Taille des cadres",
                    "ro": "Dimensiune ramă",
                    "pt": "Tamanho do quadro",
                    "es": "Tamaño de marco",
                    "da": "Rammestørrelse"
                },
                "unit": null
            },
            {
                "id": 84,
                "type": "system",
                "name": "configuration",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Configuration",
                    "nl": "Samenstelling",
                    "de": "Konfiguration",
                    "fr": "Configuration",
                    "ro": "Configurație",
                    "pt": "Configuração",
                    "es": "Configuración",
                    "da": "Opbygning"
                },
                "unit": null
            },
            {
                "id": 136,
                "type": "system",
                "name": "location",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Location",
                    "nl": "Locatie",
                    "de": "Ort",
                    "fr": "Lieux",
                    "ro": "Locație",
                    "pt": "Localização",
                    "es": "Ubicación",
                    "da": "Lokation"
                },
                "unit": null
            },
            {
                "id": 614,
                "type": "checklist",
                "name": "weight",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "number_2_decimals",
                "trans": {
                    "en": "Weight",
                    "nl": "Gewicht",
                    "de": "Gewicht",
                    "fr": "Poids",
                    "ro": "Greutate",
                    "pt": "Peso",
                    "es": "Peso",
                    "da": "Vægt"
                },
                "unit": "kg"
            },
            {
                "id": 795,
                "type": "system",
                "name": "photo",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "image",
                "trans": {
                    "en": "Photo",
                    "nl": "Foto",
                    "de": "Foto",
                    "fr": "Photo",
                    "ro": "Poză",
                    "pt": "Fotografia",
                    "es": "Foto",
                    "da": "Foto"
                },
                "unit": null
            },
            {
                "id": 818,
                "type": "system",
                "name": "app",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "list",
                "trans": {
                    "nl": "App",
                    "en": "App",
                    "de": "App",
                    "fr": "App",
                    "ro": "Aplicație",
                    "pt": "Aplicação (app)",
                    "es": "App",
                    "da": "App"
                },
                "unit": null
            }
        ]
    },
    {
        "id": 149,
        "type": "list",
        "name": "bee_colony",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "label",
        "trans": {
            "en": "Bee colony",
            "nl": "Bijenvolk",
            "de": "Bienenvolk",
            "fr": "Colonie",
            "ro": "Colonie de albine",
            "pt": "Colónia",
            "es": "Colonia de abejas",
            "da": "Bifamilie"
        },
        "unit": null,
        "children": [
            {
                "id": 73,
                "type": "checklist",
                "name": "space",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Space",
                    "nl": "Ruimte",
                    "de": "Platz",
                    "fr": "Espacement",
                    "ro": "Spațiu",
                    "pt": "Espaço",
                    "es": "Espacio",
                    "da": "Mellemrum"
                },
                "unit": null
            },
            {
                "id": 150,
                "type": "system",
                "name": "origin",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Origin",
                    "nl": "Oorsprong",
                    "de": "Ursprung",
                    "fr": "Origine",
                    "ro": "Origine",
                    "pt": "Origem",
                    "es": "Origen",
                    "da": "Oprindelse"
                },
                "unit": null
            },
            {
                "id": 165,
                "type": "list",
                "name": "activity",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Activity",
                    "nl": "Activiteit",
                    "de": "Aktivität",
                    "fr": "Activité",
                    "ro": "Activitate",
                    "pt": "Actividade",
                    "es": "Actividad",
                    "da": "Aktivitet"
                },
                "unit": null
            },
            {
                "id": 208,
                "type": "system",
                "name": "status",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Status",
                    "nl": "Status",
                    "de": "Status",
                    "fr": "Statut",
                    "ro": "Stare",
                    "pt": "Estado",
                    "es": "Estado",
                    "da": "Status"
                },
                "unit": null
            },
            {
                "id": 213,
                "type": "list",
                "name": "characteristics",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Characteristics",
                    "nl": "Eigenschappen",
                    "de": "Charakteristiken",
                    "fr": "Caracteristique",
                    "ro": "Caracteristici",
                    "pt": "Características",
                    "es": "Características",
                    "da": "Egenskaber"
                },
                "unit": null
            },
            {
                "id": 253,
                "type": "checklist",
                "name": "swarm_prevention",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Swarm prevention",
                    "nl": "Zwermverhindering",
                    "de": "Schwarmverhinderung",
                    "fr": "Prévention de l'essaimage",
                    "ro": "Prevenirea roirii",
                    "pt": "Prevenção de enxameamento",
                    "es": "Prevención de enjambrazón",
                    "da": "Sværmehindring"
                },
                "unit": null
            },
            {
                "id": 263,
                "type": "list",
                "name": "brood",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Brood",
                    "nl": "Broed",
                    "de": "Brut",
                    "fr": "Couvain",
                    "ro": "Puiet",
                    "pt": "Criação",
                    "es": "Cría",
                    "da": "Yngel"
                },
                "unit": null
            },
            {
                "id": 333,
                "type": "checklist",
                "name": "queen",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Queen",
                    "nl": "Moer",
                    "de": "Königin",
                    "fr": "Reine",
                    "ro": "Matcă",
                    "pt": "Raínha",
                    "es": "Reina",
                    "da": "Dronning"
                },
                "unit": null
            },
            {
                "id": 442,
                "type": "list",
                "name": "drones",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Drones",
                    "nl": "Darren",
                    "de": "Drohnen",
                    "fr": "Mâles",
                    "ro": "Trântori",
                    "pt": "Zangões",
                    "es": "Zánganos",
                    "da": "Droner"
                },
                "unit": null
            },
            {
                "id": 448,
                "type": "checklist",
                "name": "uniting_colonies",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Uniting colonies",
                    "nl": "Volken samenvoegen",
                    "de": "Volksvereinigung",
                    "fr": "Reunion de colonies",
                    "ro": "Unificare colonii",
                    "pt": "União de colónias",
                    "es": "Colmenas fusionadas",
                    "da": "Samling af bifamilier"
                },
                "unit": null
            },
            {
                "id": 453,
                "type": "list",
                "name": "bees_added",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Bees added",
                    "nl": "Bijen toegevoegd",
                    "de": "Bienen hinzugefügt",
                    "fr": "Ajout d'abeille",
                    "ro": "Albine adăugate",
                    "pt": "Abelhas adicionadas",
                    "es": "Abejas agregadas",
                    "da": "Bier tilføjet"
                },
                "unit": null
            },
            {
                "id": 459,
                "type": "list",
                "name": "loss",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Loss",
                    "nl": "Verlies",
                    "de": "Verluste",
                    "fr": "Perdu",
                    "ro": "Pierderi",
                    "pt": "Perdas",
                    "es": "Pérdida",
                    "da": "Tab"
                },
                "unit": null
            },
            {
                "id": 472,
                "type": "list",
                "name": "removal",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Removal",
                    "nl": "Verwijdering",
                    "de": "Entfernt",
                    "fr": "Suppression",
                    "ro": "Înlăturare",
                    "pt": "Remoção",
                    "es": "Remoción",
                    "da": "Fjernelse"
                },
                "unit": null
            },
            {
                "id": 755,
                "type": "system",
                "name": "reminder",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "nl": "Herinnnering",
                    "en": "Reminder",
                    "de": "Erinnerung",
                    "fr": "Rappel",
                    "ro": "Aducere aminte",
                    "pt": "Lembrete",
                    "es": "Recordatorio",
                    "da": "Påmindelse"
                },
                "unit": null
            },
            {
                "id": 771,
                "type": "checklist",
                "name": "size",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "nl": "Grootte",
                    "en": "Size",
                    "de": "Größe",
                    "fr": "Taille",
                    "ro": "Mărime",
                    "pt": "Tamanho",
                    "es": "Tamaño",
                    "da": "Størrelse"
                },
                "unit": null
            },
            {
                "id": 776,
                "type": "checklist",
                "name": "splitting_colony",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "nl": "Volk splitsen",
                    "en": "Splitting colony",
                    "de": "Aufgeteiltes Volk",
                    "fr": "Division de colonie",
                    "ro": "Împărțirea coloniei",
                    "pt": "Colónia desdobrada",
                    "es": "División de colonia",
                    "da": "Opdeling af bifamilie"
                },
                "unit": null
            },
            {
                "id": 867,
                "type": "system",
                "name": "purpose",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Purpose",
                    "nl": "Doel",
                    "de": "Zweck",
                    "fr": "Raison",
                    "ro": "Scop",
                    "pt": "Propósito",
                    "es": "Propósito",
                    "da": "Formål"
                },
                "unit": null
            },
            {
                "id": 960,
                "type": "checklist",
                "name": "comb_replacement",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Comb replacement",
                    "nl": "Raat vervanging",
                    "de": "Wabenerneuerung",
                    "fr": "Remplacement de rayon",
                    "ro": "Înlocuirea fagurelui",
                    "pt": "Substituição de favos",
                    "es": "Reemplazo de panal",
                    "da": "Tavleudskiftning"
                },
                "unit": null
            }
        ]
    },
    {
        "id": 475,
        "type": "list",
        "name": "food",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "label",
        "trans": {
            "en": "Food",
            "nl": "Voedsel",
            "de": "Futter",
            "fr": "Nourriture",
            "ro": "Hrană",
            "pt": "Comida",
            "es": "Alimento",
            "da": "Føde"
        },
        "unit": null,
        "children": [
            {
                "id": 476,
                "type": "checklist",
                "name": "feeding",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Feeding",
                    "nl": "Bijvoeren",
                    "de": "Fütterung",
                    "fr": "Nourrissement",
                    "ro": "Hrănire",
                    "pt": "Alimentação",
                    "es": "Alimentación",
                    "da": "Fodring"
                },
                "unit": null
            },
            {
                "id": 493,
                "type": "checklist",
                "name": "stock",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Stock",
                    "nl": "Voorraad",
                    "de": "Vorrat",
                    "fr": "Stock",
                    "ro": "Stoc",
                    "pt": "Stock",
                    "es": "Stock",
                    "da": "Lager"
                },
                "unit": null
            },
            {
                "id": 500,
                "type": "list",
                "name": "forage",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Forage",
                    "nl": "Dracht",
                    "de": "Futter",
                    "fr": "Butinage",
                    "ro": "Cules",
                    "pt": "Forrageamento",
                    "es": "Forraje"
                },
                "unit": null
            },
            {
                "id": 891,
                "type": "checklist",
                "name": "water",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Water",
                    "nl": "Water",
                    "de": "Wasser",
                    "fr": "Eau",
                    "ro": "Apă",
                    "pt": "Água",
                    "es": "Agua",
                    "da": "Vand"
                },
                "unit": null
            }
        ]
    },
    {
        "id": 513,
        "type": "list",
        "name": "disorder",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "label",
        "trans": {
            "en": "Disorder",
            "nl": "Aandoening",
            "de": "Störung",
            "fr": "Problème",
            "ro": "Boală",
            "pt": "Problemas",
            "es": "Problema",
            "da": "Sygdom"
        },
        "unit": null,
        "children": [
            {
                "id": 514,
                "type": "list",
                "name": "type",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Type",
                    "nl": "Type",
                    "de": "Typ",
                    "fr": "type",
                    "ro": "Tip",
                    "pt": "Tipo",
                    "es": "Tipo",
                    "da": "Type"
                },
                "unit": null
            },
            {
                "id": 582,
                "type": "list",
                "name": "severity",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "score_amount",
                "trans": {
                    "en": "Severity",
                    "nl": "Ernst",
                    "de": "Schweregrad",
                    "fr": "Sévérité",
                    "ro": "Severitate",
                    "pt": "Severidade",
                    "da": "Alvorlighed"
                },
                "unit": null
            },
            {
                "id": 589,
                "type": "checklist",
                "name": "laboratory_test",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Laboratory test",
                    "nl": "Laboratorium test",
                    "de": "Labortest",
                    "fr": "Test laboratoire",
                    "ro": "Test de laborator",
                    "pt": "Teste laboratorial",
                    "es": "Test de laboratorio",
                    "da": "Laboratorietest"
                },
                "unit": null
            },
            {
                "id": 594,
                "type": "list",
                "name": "treatment",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Treatment",
                    "nl": "Behandeling",
                    "de": "Behandlung",
                    "fr": "Traitement",
                    "ro": "Tratament",
                    "pt": "Tratamento",
                    "es": "Tratamiento",
                    "da": "Behandling"
                },
                "unit": null
            },
            {
                "id": 830,
                "type": "checklist",
                "name": "varroa",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Varroa",
                    "nl": "Varroa",
                    "de": "Varoa",
                    "fr": "Varroa",
                    "ro": "Varroa",
                    "pt": "Varroa",
                    "es": "Varroa",
                    "da": "Varroa"
                },
                "unit": null
            }
        ]
    },
    {
        "id": 612,
        "type": "checklist",
        "name": "weather",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "label",
        "trans": {
            "en": "Weather",
            "nl": "Weer",
            "de": "Wetter",
            "fr": "Météo",
            "ro": "Vreme",
            "pt": "Clima",
            "es": "Tiempo atmosférico",
            "da": "Vejr"
        },
        "unit": null,
        "children": [
            {
                "id": 615,
                "type": "checklist",
                "name": "ambient_temperature",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "number",
                "trans": {
                    "en": "Ambient temperature",
                    "nl": "Omgevingstemperatuur",
                    "de": "Umgebungstemperatur",
                    "fr": "Température ambiante",
                    "ro": "Temperatura ambientală",
                    "pt": "Temperatura ambiente",
                    "es": "Temperatura ambiental",
                    "da": "Omgivelsestemperatur"
                },
                "unit": "°C"
            },
            {
                "id": 620,
                "type": "checklist",
                "name": "humidity",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "number_percentage",
                "trans": {
                    "en": "Humidity",
                    "nl": "Luchtvochtigheid",
                    "de": "Feuchtigkeit",
                    "fr": "Humidité",
                    "ro": "Umiditate",
                    "pt": "Humidade",
                    "es": "Humedad",
                    "da": "Fugtighed"
                },
                "unit": "%"
            },
            {
                "id": 621,
                "type": "checklist",
                "name": "cloud_cover",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "options",
                "trans": {
                    "en": "Cloud cover",
                    "nl": "Wolken",
                    "de": "Wolkendecke",
                    "fr": "Couverture nuageuse",
                    "ro": "Nebulozitate",
                    "pt": "Nebulosidade",
                    "es": "Cubierto de nubes",
                    "da": "Skydække"
                },
                "unit": null
            },
            {
                "id": 628,
                "type": "checklist",
                "name": "wind",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "number",
                "trans": {
                    "en": "Wind",
                    "nl": "Wind",
                    "de": "Wind",
                    "fr": "Vent",
                    "ro": "Vânt",
                    "pt": "Vento",
                    "es": "Viento",
                    "da": "Vind"
                },
                "unit": "bft"
            },
            {
                "id": 629,
                "type": "checklist",
                "name": "precipitation",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "number_positive",
                "trans": {
                    "en": "Precipitation",
                    "nl": "Neerslag",
                    "de": "Niederschlag",
                    "fr": "Précipitation",
                    "ro": "Precipitații",
                    "pt": "Precipitação",
                    "es": "Precipitación",
                    "da": "Nedbør"
                },
                "unit": "mm"
            }
        ]
    },
    {
        "id": 658,
        "type": "system",
        "name": "beekeeper",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "list",
        "trans": {
            "en": "Beekeeper",
            "nl": "Imker",
            "de": "Imker",
            "fr": "Apiculteur",
            "ro": "Apicultor",
            "pt": "Apicultor",
            "es": "Apicultor(a)",
            "da": "Biavler"
        },
        "unit": null,
        "children": [
            {
                "id": 659,
                "type": "0",
                "name": "name",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "boolean",
                "trans": {
                    "en": "Name",
                    "nl": "Naam",
                    "de": "Name",
                    "fr": "Nom",
                    "ro": "Nume",
                    "pt": "Nome",
                    "es": "Nombre",
                    "da": "Navn"
                },
                "unit": null
            },
            {
                "id": 660,
                "type": "list",
                "name": "location",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "select_location",
                "trans": {
                    "en": "Location",
                    "nl": "Locatie",
                    "de": "Ort",
                    "fr": "Lieux",
                    "ro": "Locație",
                    "pt": "Localização",
                    "es": "Ubicación",
                    "da": "Lokation"
                },
                "unit": null
            },
            {
                "id": 666,
                "type": "1",
                "name": "telephone",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "list_item",
                "trans": {
                    "en": "Telephone",
                    "nl": "Telefoon",
                    "de": "Telefon",
                    "fr": "Telephone",
                    "ro": "Telefon",
                    "pt": "Telefone",
                    "es": "Teléfono",
                    "da": "Telefon"
                },
                "unit": null
            },
            {
                "id": 667,
                "type": "2",
                "name": "email",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "list_item",
                "trans": {
                    "en": "Email",
                    "nl": "Email",
                    "de": "Email",
                    "fr": "Email",
                    "ro": "Email",
                    "pt": "Email",
                    "es": "Email",
                    "da": "Email"
                },
                "unit": null
            },
            {
                "id": 668,
                "type": "3",
                "name": "date_of_birth",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "date",
                "trans": {
                    "en": "Date of birth",
                    "nl": "Geboortedatum",
                    "de": "Geburtsdatum",
                    "fr": "Date de naissance",
                    "ro": "Data nașterii",
                    "pt": "Data de nascimento",
                    "es": "Fecha de Nacimiento",
                    "da": "Fødselsdato"
                },
                "unit": null
            },
            {
                "id": 669,
                "type": "4",
                "name": "gender",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "list_item",
                "trans": {
                    "en": "Gender",
                    "nl": "Geslacht",
                    "de": "Geschlecht",
                    "fr": "Genre",
                    "ro": "Gen",
                    "pt": "Género",
                    "es": "Género",
                    "da": "Køn"
                },
                "unit": null
            },
            {
                "id": 670,
                "type": "list",
                "name": "beekeeper_since",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Beekeeper since",
                    "nl": "Imker sinds",
                    "de": "Imker seit",
                    "fr": "Apiculteur depuis",
                    "ro": "Apicultor din",
                    "pt": "Apicultor desde",
                    "es": "Apicultor desde",
                    "da": "Biavler siden"
                },
                "unit": null
            },
            {
                "id": 672,
                "type": "5",
                "name": "beekeeper_id",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "text",
                "trans": {
                    "en": "Beekeeper ID",
                    "nl": "Imker ID",
                    "de": "Imker ID",
                    "fr": "ID apiculteur",
                    "ro": "ID apicultor",
                    "pt": "Número de apicultor",
                    "es": "ID Apicultor",
                    "da": "Biavler ID"
                },
                "unit": null
            },
            {
                "id": 673,
                "type": "list",
                "name": "company",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Company",
                    "nl": "Bedrijf",
                    "de": "Betrieb",
                    "fr": "Société",
                    "ro": "Companie",
                    "pt": "Empresa",
                    "es": "Compañía\/Empresa",
                    "da": "Firma"
                },
                "unit": null
            },
            {
                "id": 680,
                "type": "list",
                "name": "method",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "options",
                "trans": {
                    "en": "Method",
                    "nl": "Methode",
                    "de": "Methode",
                    "fr": "Méthode",
                    "ro": "Metodă",
                    "pt": "Método",
                    "es": "Método",
                    "da": "Metode"
                },
                "unit": null
            },
            {
                "id": 688,
                "type": "list",
                "name": "role",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "options",
                "trans": {
                    "en": "Role",
                    "nl": "Rol",
                    "fr": "Rôle",
                    "ro": "Rol",
                    "pt": "Papel",
                    "es": "Rol",
                    "da": "Rolle"
                },
                "unit": null
            },
            {
                "id": 691,
                "type": "system",
                "name": "photo",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "list_item",
                "trans": {
                    "en": "Photo",
                    "nl": "Foto",
                    "de": "Foto",
                    "fr": "Photo",
                    "ro": "Poză",
                    "pt": "Fotografia",
                    "es": "Foto",
                    "da": "Foto"
                },
                "unit": null
            },
            {
                "id": 692,
                "type": "list",
                "name": "inspection_role",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Inspection role",
                    "nl": "Inspectie rol",
                    "fr": "Rôle d'inspection",
                    "ro": "Scopul inspecției",
                    "pt": "Inspecção",
                    "es": "Rol durante la inspección"
                },
                "unit": null
            }
        ]
    },
    {
        "id": 698,
        "type": "list",
        "name": "production",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "label",
        "trans": {
            "en": "Production",
            "nl": "Productie",
            "de": "Produktion",
            "fr": "Production",
            "ro": "Producție",
            "pt": "Produção",
            "es": "Producción",
            "da": "Produktion"
        },
        "unit": null,
        "children": [
            {
                "id": 851,
                "type": "checklist",
                "name": "honey",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Honey",
                    "nl": "Honing",
                    "de": "Honig",
                    "fr": "Miel",
                    "ro": "Miere",
                    "pt": "Mel",
                    "es": "Miel",
                    "da": "Honning"
                },
                "unit": null
            },
            {
                "id": 852,
                "type": "checklist",
                "name": "other",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Other",
                    "nl": "Andere",
                    "de": "andere",
                    "fr": "Autre",
                    "ro": "Alte",
                    "pt": "Outros",
                    "es": "Otro",
                    "da": "Andet"
                },
                "unit": null
            }
        ]
    }
]
```
> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/categories`


<!-- END_109013899e0bc43247b0f00b67f889cf -->

<!-- START_34925c1e31e7ecc53f8f52c8b1e91d44 -->
## api/categories/{id}
Display the specified category.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/categories/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/categories/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/categories/{category}`


<!-- END_34925c1e31e7ecc53f8f52c8b1e91d44 -->

#Api\ChecklistController


<!-- START_a822c84c5aed22599b5c0f93e2ce41c1 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/checklists" 
```

```javascript
const url = new URL("https://test.beep.nl/api/checklists");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/checklists`


<!-- END_a822c84c5aed22599b5c0f93e2ce41c1 -->

<!-- START_3e3f29f45650477fd4a18d781913f05b -->
## api/checklists
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/checklists" 
```

```javascript
const url = new URL("https://test.beep.nl/api/checklists");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/checklists`


<!-- END_3e3f29f45650477fd4a18d781913f05b -->

<!-- START_c5e0350854444e0a45c0b055503dc0a4 -->
## api/checklists/{checklist}
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/checklists/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/checklists/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/checklists/{checklist}`


<!-- END_c5e0350854444e0a45c0b055503dc0a4 -->

<!-- START_794ee76fd055e386962fe5ec27954b9d -->
## api/checklists/{checklist}
> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/checklists/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/checklists/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/checklists/{checklist}`

`PATCH api/checklists/{checklist}`


<!-- END_794ee76fd055e386962fe5ec27954b9d -->

<!-- START_4e47488f23358012f46746d57d9c46b0 -->
## api/checklists/{checklist}
> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/checklists/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/checklists/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/checklists/{checklist}`


<!-- END_4e47488f23358012f46746d57d9c46b0 -->

#Api\DeviceController

Store and retreive Devices that produce measurements
<!-- START_1221b770dd464496433a0d3d92f88d37 -->
## api/devices/multiple POST
Store/update multiple Devices in an array of Device objects

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/devices/multiple" \
    -H "Content-Type: application/json" \
    -d '{"key":"sunt","name":"aut","hive_id":1,"type":"ut","last_message_received":"qui","hardware_id":"quos","firmware_version":"voluptatum","hardware_version":"hic","boot_count":5,"measurement_interval_min":74823932.23,"measurement_transmission_ratio":2598002.3716408405,"ble_pin":"doloremque","battery_voltage":927.105757903,"next_downlink_message":"quo","last_downlink_result":"ut"}'

```

```javascript
const url = new URL("https://test.beep.nl/api/devices/multiple");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key": "sunt",
    "name": "aut",
    "hive_id": 1,
    "type": "ut",
    "last_message_received": "qui",
    "hardware_id": "quos",
    "firmware_version": "voluptatum",
    "hardware_version": "hic",
    "boot_count": 5,
    "measurement_interval_min": 74823932.23,
    "measurement_transmission_ratio": 2598002.3716408405,
    "ble_pin": "doloremque",
    "battery_voltage": 927.105757903,
    "next_downlink_message": "quo",
    "last_downlink_result": "ut"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/devices/multiple`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key | string |  required  | DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint
    name | string |  optional  | Device name
    hive_id | integer |  optional  | Hive that the sensor is measuring. Default: null
    type | string |  optional  | Category name of the hive type from the Categories table. Default: beep
    last_message_received | timestamp |  optional  | Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    hardware_id | string |  optional  | Unchangeable Device id
    firmware_version | string |  optional  | Firmware version of the Device
    hardware_version | string |  optional  | Hardware version of the Device
    boot_count | integer |  optional  | Amount of boots of the Device
    measurement_interval_min | float |  optional  | Measurement interval in minutes
    measurement_transmission_ratio | float |  optional  | Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    ble_pin | string |  optional  | Bleutooth PIN of Device: 6 numbers between 0-9
    battery_voltage | float |  optional  | Last measured battery voltage
    next_downlink_message | string |  optional  | Hex string to send via downlink at next connection (LoRaWAN port 6)
    last_downlink_result | string |  optional  | Result received from BEEP base after downlink message (LoRaWAN port 5)

<!-- END_1221b770dd464496433a0d3d92f88d37 -->

<!-- START_1e05262d240dcd7c5c277be4411cdd41 -->
## api/devices/ttn/{dev_id} GET
Get a TTN Device by Device ID (BEEP hardware_id)

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/devices/ttn/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/devices/ttn/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/devices/ttn/{dev_id}`


<!-- END_1e05262d240dcd7c5c277be4411cdd41 -->

<!-- START_52c49726ab742e3eb07b25ed65074e78 -->
## api/devices/ttn/{dev_id} POST
Create an OTAA LoRaWAN Device in the BEEP TTN Console by dev_id (dev_id (= BEEP hardware_id) a unique identifier for the device. It can contain lowercase letters, numbers, - and _) and this payload:
{
&quot;lorawan_device&quot;: {
&quot;dev_eui&quot;: &quot;&lt;8 byte identifier for the device&gt;&quot;,
&quot;app_key&quot;: &quot;&lt;16 byte static key that is known by the device and the application. It is used for negotiating session keys (OTAA)&gt;&quot;
}
}

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/devices/ttn/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/devices/ttn/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/devices/ttn/{dev_id}`


<!-- END_52c49726ab742e3eb07b25ed65074e78 -->

<!-- START_8f41217bf023ddef5d8995d7c7c7e2e2 -->
## api/devices GET
List all user Devices

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/devices" \
    -H "Content-Type: application/json" \
    -d '{"hardware_id":"est"}'

```

```javascript
const url = new URL("https://test.beep.nl/api/devices");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "hardware_id": "est"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
[
    {
        "id": 1,
        "hive_id": 2,
        "name": "BEEPBASE-0000",
        "key": "000000000000000",
        "created_at": "2020-01-22 09:43:03",
        "last_message_received": null,
        "hardware_id": null,
        "firmware_version": null,
        "hardware_version": null,
        "boot_count": null,
        "measurement_interval_min": null,
        "measurement_transmission_ratio": null,
        "ble_pin": null,
        "battery_voltage": null,
        "next_downlink_message": null,
        "last_downlink_result": null,
        "type": "beep",
        "hive_name": "Hive 2",
        "location_name": "Test stand 1",
        "owner": true,
        "sensor_definitions": [
            {
                "id": 7,
                "name": null,
                "inside": null,
                "offset": 8131,
                "multiplier": null,
                "input_measurement_id": 7,
                "output_measurement_id": 20,
                "device_id": 1,
                "input_abbr": "w_v",
                "output_abbr": "weight_kg"
            }
        ]
    }
]
```
> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/devices`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    hardware_id | string |  optional  | Provide to filter on hardware_id

<!-- END_8f41217bf023ddef5d8995d7c7c7e2e2 -->

<!-- START_dbb21adf8d2a4b13a3c3113b57e8d329 -->
## api/devices POST
Create or Update a Device

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/devices" \
    -H "Content-Type: application/json" \
    -d '{"key":"consectetur","name":"enim","hive_id":1,"type":"quia","last_message_received":"tempore","hardware_id":"fuga","firmware_version":"officia","hardware_version":"asperiores","boot_count":18,"measurement_interval_min":7,"measurement_transmission_ratio":118027014.178005,"ble_pin":"sint","battery_voltage":233.747,"next_downlink_message":"nisi","last_downlink_result":"tempore","create_ttn_device":true,"app_key":"maiores"}'

```

```javascript
const url = new URL("https://test.beep.nl/api/devices");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key": "consectetur",
    "name": "enim",
    "hive_id": 1,
    "type": "quia",
    "last_message_received": "tempore",
    "hardware_id": "fuga",
    "firmware_version": "officia",
    "hardware_version": "asperiores",
    "boot_count": 18,
    "measurement_interval_min": 7,
    "measurement_transmission_ratio": 118027014.178005,
    "ble_pin": "sint",
    "battery_voltage": 233.747,
    "next_downlink_message": "nisi",
    "last_downlink_result": "tempore",
    "create_ttn_device": true,
    "app_key": "maiores"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/devices`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key | string |  required  | DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint
    name | string |  optional  | Device name
    hive_id | integer |  optional  | Hive that the sensor is measuring. Default: null
    type | string |  optional  | Category name of the hive type from the Categories table. Default: beep
    last_message_received | timestamp |  optional  | Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    hardware_id | string |  optional  | Unchangeable Device id
    firmware_version | string |  optional  | Firmware version of the Device
    hardware_version | string |  optional  | Hardware version of the Device
    boot_count | integer |  optional  | Amount of boots of the Device
    measurement_interval_min | float |  optional  | Measurement interval in minutes
    measurement_transmission_ratio | float |  optional  | Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    ble_pin | string |  optional  | Bleutooth PIN of Device: 6 numbers between 0-9
    battery_voltage | float |  optional  | Last measured battery voltage
    next_downlink_message | string |  optional  | Hex string to send via downlink at next connection (LoRaWAN port 6)
    last_downlink_result | string |  optional  | Result received from BEEP base after downlink message (LoRaWAN port 5)
    create_ttn_device | boolean |  optional  | If true, create a new LoRaWAN device in the BEEP TTN console. If succesfull, create the device.
    app_key | string |  optional  | BEEP base LoRaWAN application key that you would like to store in TTN

<!-- END_dbb21adf8d2a4b13a3c3113b57e8d329 -->

<!-- START_b50165d5ab057bad7bcff39a66a40cf1 -->
## api/devices/{id} GET
List one Device by id

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/devices/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/devices/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/devices/{device}`


<!-- END_b50165d5ab057bad7bcff39a66a40cf1 -->

<!-- START_52b9480c37d5f861392515f99f114a2c -->
## api/devices PUT/PATCH
Update an existing Device

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/devices/1" \
    -H "Content-Type: application/json" \
    -d '{"id":20,"key":"voluptate","name":"ipsa","hive_id":15,"type":"provident","delete":true,"last_message_received":"nemo","hardware_id":"neque","firmware_version":"pariatur","hardware_version":"non","boot_count":5,"measurement_interval_min":3.156,"measurement_transmission_ratio":2,"ble_pin":"amet","battery_voltage":36838049,"next_downlink_message":"aliquam","last_downlink_result":"perspiciatis"}'

```

```javascript
const url = new URL("https://test.beep.nl/api/devices/1");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "id": 20,
    "key": "voluptate",
    "name": "ipsa",
    "hive_id": 15,
    "type": "provident",
    "delete": true,
    "last_message_received": "nemo",
    "hardware_id": "neque",
    "firmware_version": "pariatur",
    "hardware_version": "non",
    "boot_count": 5,
    "measurement_interval_min": 3.156,
    "measurement_transmission_ratio": 2,
    "ble_pin": "amet",
    "battery_voltage": 36838049,
    "next_downlink_message": "aliquam",
    "last_downlink_result": "perspiciatis"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/devices/{device}`

`PATCH api/devices/{device}`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    id | integer |  required  | Device to update
    key | string |  required  | DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint
    name | string |  optional  | Name of the sensor
    hive_id | integer |  optional  | Hive that the sensor is measuring. Default: null
    type | string |  optional  | Category name of the hive type from the Categories table. Default: beep
    delete | boolean |  optional  | If true delete the sensor and all it's data in the Influx database
    last_message_received | timestamp |  optional  | Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    hardware_id | string |  optional  | Unchangeable Device id
    firmware_version | string |  optional  | Firmware version of the Device
    hardware_version | string |  optional  | Hardware version of the Device
    boot_count | integer |  optional  | Amount of boots of the Device
    measurement_interval_min | float |  optional  | Measurement interval in minutes
    measurement_transmission_ratio | float |  optional  | Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    ble_pin | string |  optional  | Bleutooth PIN of Device: 6 numbers between 0-9
    battery_voltage | float |  optional  | Last measured battery voltage
    next_downlink_message | string |  optional  | Hex string to send via downlink at next connection (LoRaWAN port 6)
    last_downlink_result | string |  optional  | Result received from BEEP base after downlink message (LoRaWAN port 5)

<!-- END_52b9480c37d5f861392515f99f114a2c -->

#Api\ExportController

Export all data to an Excel file by email (GDPR)
<!-- START_48911fc3cde4ec2d92e30c1511b44372 -->
## api/export
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/export" 
```

```javascript
const url = new URL("https://test.beep.nl/api/export");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/export`


<!-- END_48911fc3cde4ec2d92e30c1511b44372 -->

<!-- START_5d658b079229cb412abcf4dada818425 -->
## api/export/csv
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/export/csv" 
```

```javascript
const url = new URL("https://test.beep.nl/api/export/csv");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/export/csv`


<!-- END_5d658b079229cb412abcf4dada818425 -->

#Api\GroupController


<!-- START_d935edfdeccc953e11ef436a9d768e1c -->
## api/groups/checktoken
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/groups/checktoken" 
```

```javascript
const url = new URL("https://test.beep.nl/api/groups/checktoken");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/groups/checktoken`


<!-- END_d935edfdeccc953e11ef436a9d768e1c -->

<!-- START_007018a8a9f15c2d47fcb105431ffeee -->
## api/groups
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/groups" 
```

```javascript
const url = new URL("https://test.beep.nl/api/groups");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/groups`


<!-- END_007018a8a9f15c2d47fcb105431ffeee -->

<!-- START_15c22564ad248f952405021410fd1d25 -->
## api/groups
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/groups" 
```

```javascript
const url = new URL("https://test.beep.nl/api/groups");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/groups`


<!-- END_15c22564ad248f952405021410fd1d25 -->

<!-- START_a209a43173c7c4aaf7ab070d77fb7f0c -->
## api/groups/{group}
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/groups/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/groups/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/groups/{group}`


<!-- END_a209a43173c7c4aaf7ab070d77fb7f0c -->

<!-- START_5b84408c838201930093112a7621935c -->
## api/groups/{group}
> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/groups/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/groups/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/groups/{group}`

`PATCH api/groups/{group}`


<!-- END_5b84408c838201930093112a7621935c -->

<!-- START_bd4f731f3f84c755053406b8971eba1f -->
## api/groups/{group}
> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/groups/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/groups/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/groups/{group}`


<!-- END_bd4f731f3f84c755053406b8971eba1f -->

<!-- START_e8fabede7787762b78140f2bfd317d77 -->
## api/groups/detach/{id}
> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/groups/detach/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/groups/detach/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/groups/detach/{id}`


<!-- END_e8fabede7787762b78140f2bfd317d77 -->

#Api\HiveController


<!-- START_e5e611c362bfc5ad03913023307faa25 -->
## api/hives GET
Display a listing of user hives.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/hives" 
```

```javascript
const url = new URL("https://test.beep.nl/api/hives");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "hives": [
        {
            "id": 1,
            "location_id": 1,
            "hive_type_id": 43,
            "color": "#35f200",
            "name": "Kast 1",
            "created_at": "2017-07-13 23:34:49",
            "type": "spaarkast",
            "location": "",
            "attention": null,
            "impression": null,
            "reminder": null,
            "reminder_date": null,
            "inspection_count": 0,
            "sensors": [
                3,
                19
            ],
            "owner": true,
            "layers": [
                {
                    "id": 1,
                    "order": 0,
                    "color": "#35f200",
                    "type": "brood",
                    "framecount": 10
                },
                {
                    "id": 2,
                    "order": 1,
                    "color": "#35f200",
                    "type": "brood",
                    "framecount": 10
                },
                {
                    "id": 3,
                    "order": 2,
                    "color": "#35f200",
                    "type": "honey",
                    "framecount": 10
                }
            ],
            "queen": null
        }
    ]
}
```
> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/hives`


<!-- END_e5e611c362bfc5ad03913023307faa25 -->

<!-- START_80df36a766c9f45b8245df7a4c584eef -->
## api/hives POST
Store a newly created Hive in storage for the authenticated user.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/hives" 
```

```javascript
const url = new URL("https://test.beep.nl/api/hives");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/hives`


<!-- END_80df36a766c9f45b8245df7a4c584eef -->

<!-- START_6ec9bb72691ffb3b668ce33a42d1f9a3 -->
## api/hives/{id} GET
Display the specified resource.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/hives/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/hives/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/hives/{hive}`


<!-- END_6ec9bb72691ffb3b668ce33a42d1f9a3 -->

<!-- START_e849cd4cd54a66f21f8716c15cfba36e -->
## api/hives/{id} PATCH
Update the specified user Hive in storage.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/hives/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/hives/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/hives/{hive}`

`PATCH api/hives/{hive}`


<!-- END_e849cd4cd54a66f21f8716c15cfba36e -->

<!-- START_378a9cd4d171a263c8864789602f8fd8 -->
## api/hives/{id} DELETE
Remove the specified user Hive from storage.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/hives/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/hives/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/hives/{hive}`


<!-- END_378a9cd4d171a263c8864789602f8fd8 -->

#Api\ImageController

Store and retreive image metadata (image_url, thumb_url, width, category_id, etc.)
<!-- START_8e05289fc079261819c2c145f89215f1 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/images" 
```

```javascript
const url = new URL("https://test.beep.nl/api/images");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/images`


<!-- END_8e05289fc079261819c2c145f89215f1 -->

<!-- START_204613676cab89a55dfdc7d81f16a281 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/images" 
```

```javascript
const url = new URL("https://test.beep.nl/api/images");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/images`


<!-- END_204613676cab89a55dfdc7d81f16a281 -->

<!-- START_b72ae09f5d6ffe769e7e25847bfb4713 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/images/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/images/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/images/{image}`


<!-- END_b72ae09f5d6ffe769e7e25847bfb4713 -->

<!-- START_663d256882d5392cfe487383a4e8703e -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/images/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/images/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/images/{image}`

`PATCH api/images/{image}`


<!-- END_663d256882d5392cfe487383a4e8703e -->

<!-- START_c75b2cb29db1089e35d664b3e14b03ca -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/images" 
```

```javascript
const url = new URL("https://test.beep.nl/api/images");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/images`


<!-- END_c75b2cb29db1089e35d664b3e14b03ca -->

#Api\InspectionsController


<!-- START_ccf630a0f3579abb6ab3d47b9f4a65ab -->
## api/inspections/lists
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/inspections/lists" 
```

```javascript
const url = new URL("https://test.beep.nl/api/inspections/lists");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/inspections/lists`


<!-- END_ccf630a0f3579abb6ab3d47b9f4a65ab -->

<!-- START_8aaacf5f8f3d9bf8bff1980f0dbb99c5 -->
## api/inspections/{hive_id}
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/inspections/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/inspections/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/inspections/{hive_id}`


<!-- END_8aaacf5f8f3d9bf8bff1980f0dbb99c5 -->

<!-- START_aeffa3642b8d8eeca87b4c02f9b26262 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/inspections/hive/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/inspections/hive/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/inspections/hive/{hive_id}`


<!-- END_aeffa3642b8d8eeca87b4c02f9b26262 -->

<!-- START_eebc1891debfc7536a71b0f153ad21cd -->
## api/inspections/store
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/inspections/store" 
```

```javascript
const url = new URL("https://test.beep.nl/api/inspections/store");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/inspections/store`


<!-- END_eebc1891debfc7536a71b0f153ad21cd -->

<!-- START_78fa69f4fe2d3eed735b8d13151e5562 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/inspections/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/inspections/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/inspections/{id}`


<!-- END_78fa69f4fe2d3eed735b8d13151e5562 -->

#Api\LocationController

Manage Apiaries
<!-- START_7fb4739b1e26173b78c06ed910857f37 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/locations" 
```

```javascript
const url = new URL("https://test.beep.nl/api/locations");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/locations`


<!-- END_7fb4739b1e26173b78c06ed910857f37 -->

<!-- START_6ac6759cab929b9077bddc6d56416b5c -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/locations" 
```

```javascript
const url = new URL("https://test.beep.nl/api/locations");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/locations`


<!-- END_6ac6759cab929b9077bddc6d56416b5c -->

<!-- START_f71771a70af5f8dad2212b1b5a2258d5 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/locations/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/locations/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/locations/{location}`


<!-- END_f71771a70af5f8dad2212b1b5a2258d5 -->

<!-- START_ddb58ef8759801169efb409d19aa45da -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/locations/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/locations/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/locations/{location}`

`PATCH api/locations/{location}`


<!-- END_ddb58ef8759801169efb409d19aa45da -->

<!-- START_fa28b5e8dd2e79a38ee29df19a80f037 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/locations/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/locations/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/locations/{location}`


<!-- END_fa28b5e8dd2e79a38ee29df19a80f037 -->

#Api\MeasurementController

Store and retreive sensor data (both LoRa and direct API POSTs)
<!-- START_b3690d7048f832bb2ae7059b2ccd2d2e -->
## api/sensors POST
Store sensor measurement data (see BEEP sensor data API definition) from API, or TTN. In case of using api/unsecure_sensors, this is used for legacy measurement devices that do not have the means to encrypt HTTPS cypher

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/sensors" \
    -H "Content-Type: application/json" \
    -d '{"key":"nihil","data":[],"payload_fields":[]}'

```

```javascript
const url = new URL("https://test.beep.nl/api/sensors");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key": "nihil",
    "data": [],
    "payload_fields": []
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/sensors`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key | string |  required  | DEV EUI of the sensor (Device in Domain model) to enable storing sensor data
    data | array |  optional  | TTN Measurement data
    payload_fields | array |  optional  | TTN Measurement data

<!-- END_b3690d7048f832bb2ae7059b2ccd2d2e -->

<!-- START_493153e007f201a68c09b94906fd38fd -->
## api/lora_sensors POST
Store sensor measurement data (see BEEP sensor data API definition) from TTN or KPN (Simpoint)
When Simpoint payload is supplied, the LoRa HEX to key/value pairs decoding is done within function $this-&gt;parse_ttn_payload()
When TTN payload is supplied, the TTN HTTP integration decoder/converter is assumed to have already converted the payload from LoRa HEX to key/value conversion

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/lora_sensors" \
    -H "Content-Type: application/json" \
    -d '{"key":"ut","payload_fields":[],"DevEUI_uplink":[]}'

```

```javascript
const url = new URL("https://test.beep.nl/api/lora_sensors");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key": "ut",
    "payload_fields": [],
    "DevEUI_uplink": []
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/lora_sensors`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key | string |  required  | DEV EUI of the sensor (Device in Domain model) to enable storing sensor data
    payload_fields | array |  optional  | TTN Measurement data
    DevEUI_uplink | array |  optional  | KPN Measurement data

<!-- END_493153e007f201a68c09b94906fd38fd -->

<!-- START_bcb7a14ed838926257563c68667a27c1 -->
## api/sensors POST
Store sensor measurement data (see BEEP sensor data API definition) from API, or TTN. In case of using api/unsecure_sensors, this is used for legacy measurement devices that do not have the means to encrypt HTTPS cypher

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/unsecure_sensors" \
    -H "Content-Type: application/json" \
    -d '{"key":"sunt","data":[],"payload_fields":[]}'

```

```javascript
const url = new URL("https://test.beep.nl/api/unsecure_sensors");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key": "sunt",
    "data": [],
    "payload_fields": []
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/unsecure_sensors`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key | string |  required  | DEV EUI of the sensor (Device in Domain model) to enable storing sensor data
    data | array |  optional  | TTN Measurement data
    payload_fields | array |  optional  | TTN Measurement data

<!-- END_bcb7a14ed838926257563c68667a27c1 -->

<!-- START_f110a838c139b465d2aa0fe28eced864 -->
## api/sensors/measurements GET
Request all sensor measurements from a certain interval (hour, day, week, month, year) and index (0=until now, 1=previous interval, etc.)

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/sensors/measurements" \
    -H "Content-Type: application/json" \
    -d '{"key":"amet","id":5,"hive_id":16,"names":"architecto","interval":"in","index":4}'

```

```javascript
const url = new URL("https://test.beep.nl/api/sensors/measurements");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key": "amet",
    "id": 5,
    "hive_id": 16,
    "names": "architecto",
    "interval": "in",
    "index": 4
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/sensors/measurements`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key | string |  optional  | DEV EUI to look up the sensor (Device)
    id | integer |  optional  | ID to look up the sensor (Device)
    hive_id | integer |  optional  | Hive ID to look up the sensor (Device)
    names | string |  optional  | comma separated list of Measurement abbreviations to filter request data (weight_kg, t, h, etc.)
    interval | string |  optional  | Data interval for interpolation of measurement values: hour (2min), day (10min), week (1 hour), month (3 hours), year (1 day). Default: day.
    index | integer |  optional  | Interval index (>=0; 0=until now, 1=previous interval, etc.). Default: 0.

<!-- END_f110a838c139b465d2aa0fe28eced864 -->

<!-- START_5ed0763eff49928fbff019838d73ffce -->
## api/sensors/lastvalues GET
Request last measurement values of all sensor measurements from a sensor (Device)

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/sensors/lastvalues" \
    -H "Content-Type: application/json" \
    -d '{"key":"libero","id":1,"hive_id":5}'

```

```javascript
const url = new URL("https://test.beep.nl/api/sensors/lastvalues");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key": "libero",
    "id": 1,
    "hive_id": 5
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/sensors/lastvalues`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key | string |  optional  | DEV EUI to look up the sensor (Device)
    id | integer |  optional  | ID to look up the sensor (Device)
    hive_id | integer |  optional  | Hive ID to look up the sensor (Device)

<!-- END_5ed0763eff49928fbff019838d73ffce -->

<!-- START_cae771ed592df40719b12c9bcbf8409c -->
## api/sensors/lastweight GET
Request last weight related measurement values from a sensor (Device), used by legacy webapp to show calibration data: [&#039;w_fl&#039;, &#039;w_fr&#039;, &#039;w_bl&#039;, &#039;w_br&#039;, &#039;w_v&#039;, &#039;weight_kg&#039;, &#039;weight_kg_corrected&#039;, &#039;calibrating_weight&#039;, &#039;w_v_offset&#039;, &#039;w_v_kg_per_val&#039;, &#039;w_fl_offset&#039;, &#039;w_fr_offset&#039;, &#039;w_bl_offset&#039;, &#039;w_br_offset&#039;]

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/sensors/lastweight" \
    -H "Content-Type: application/json" \
    -d '{"key":"quibusdam","id":18,"hive_id":11}'

```

```javascript
const url = new URL("https://test.beep.nl/api/sensors/lastweight");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key": "quibusdam",
    "id": 18,
    "hive_id": 11
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/sensors/lastweight`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key | string |  optional  | DEV EUI to look up the sensor (Device)
    id | integer |  optional  | ID to look up the sensor (Device)
    hive_id | integer |  optional  | Hive ID to look up the sensor (Device)

<!-- END_cae771ed592df40719b12c9bcbf8409c -->

<!-- START_6a462f7d1eb1f0e0377d2a3d717ab5f2 -->
## api/sensors/calibrateweight
Legacy method, used by legacy webapp to store weight calibration value e.g.[w_v_kg_per_val] in Influx database, to lookup and calculate [weight_kg] at incoming measurement value storage

At the next measurement coming in, calibrate each weight sensor with it's part of a given weight.
Because the measurements can come in only each hour/ 3hrs, set a value to trigger the calculation on next measurement

1. If $next_measurement == true: save 'calibrating' = true in Influx with the sensor key
2. If $next_measurement == false: save 'calibrating' = false in Influx with the sensor key and...
3.   Get the last measured weight values for this sensor key,
     Divide the given weight (in kg) with the amount of sensor values > 1.0 (assuming the weight is evenly distributed)
     Calculate the multiplier per sensor by dividing the multiplier = weight_part / (value - offset)
     Save the multiplier as $device_name.'_kg_per_val' in Influx

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/sensors/calibrateweight" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensors/calibrateweight");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/sensors/calibrateweight`


<!-- END_6a462f7d1eb1f0e0377d2a3d717ab5f2 -->

<!-- START_458cef5981d281650d0312a814c62a17 -->
## api/sensors/offsetweight
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/sensors/offsetweight" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensors/offsetweight");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/sensors/offsetweight`


<!-- END_458cef5981d281650d0312a814c62a17 -->

<!-- START_6a44743eac9c5405a257ac407399e973 -->
## api/sensors/measurement_types_available
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/sensors/measurement_types_available" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensors/measurement_types_available");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/sensors/measurement_types_available`


<!-- END_6a44743eac9c5405a257ac407399e973 -->

<!-- START_4f42521a60d8bb82d22897367044bff3 -->
## api/lora_sensors POST
Store sensor measurement data (see BEEP sensor data API definition) from TTN or KPN (Simpoint)
When Simpoint payload is supplied, the LoRa HEX to key/value pairs decoding is done within function $this-&gt;parse_ttn_payload()
When TTN payload is supplied, the TTN HTTP integration decoder/converter is assumed to have already converted the payload from LoRa HEX to key/value conversion

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/lora_sensors_auth" \
    -H "Content-Type: application/json" \
    -d '{"key":"deleniti","payload_fields":[],"DevEUI_uplink":[]}'

```

```javascript
const url = new URL("https://test.beep.nl/api/lora_sensors_auth");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key": "deleniti",
    "payload_fields": [],
    "DevEUI_uplink": []
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/lora_sensors_auth`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key | string |  required  | DEV EUI of the sensor (Device in Domain model) to enable storing sensor data
    payload_fields | array |  optional  | TTN Measurement data
    DevEUI_uplink | array |  optional  | KPN Measurement data

<!-- END_4f42521a60d8bb82d22897367044bff3 -->

#Api\ProductionController

Not used
<!-- START_3dd824ece488b084ec5fce9c8c42eb30 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/productions" 
```

```javascript
const url = new URL("https://test.beep.nl/api/productions");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/productions`


<!-- END_3dd824ece488b084ec5fce9c8c42eb30 -->

<!-- START_67731ff70fa546e138dc8c8551071814 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/productions" 
```

```javascript
const url = new URL("https://test.beep.nl/api/productions");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/productions`


<!-- END_67731ff70fa546e138dc8c8551071814 -->

<!-- START_434cf4b94a43998d13abf7311b0946aa -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/productions/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/productions/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/productions/{production}`


<!-- END_434cf4b94a43998d13abf7311b0946aa -->

<!-- START_03405779f48975554a16a3c4fd5dc2d9 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/productions/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/productions/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/productions/{production}`

`PATCH api/productions/{production}`


<!-- END_03405779f48975554a16a3c4fd5dc2d9 -->

<!-- START_8ac584ff49e084887e1540dae0dbd539 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/productions/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/productions/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/productions/{production}`


<!-- END_8ac584ff49e084887e1540dae0dbd539 -->

#Api\QueenController

Not used
<!-- START_6617e742ea9a08d8b9eb9c7b254615de -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/queens" 
```

```javascript
const url = new URL("https://test.beep.nl/api/queens");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/queens`


<!-- END_6617e742ea9a08d8b9eb9c7b254615de -->

<!-- START_af48b21ffd9099e4fe417ced8d762f7a -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/queens" 
```

```javascript
const url = new URL("https://test.beep.nl/api/queens");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/queens`


<!-- END_af48b21ffd9099e4fe417ced8d762f7a -->

<!-- START_a92861a394debdbec3528ef3c26d8a22 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/queens/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/queens/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/queens/{queen}`


<!-- END_a92861a394debdbec3528ef3c26d8a22 -->

<!-- START_20c827dbbb9d7175c20551b24a9b21bc -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/queens/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/queens/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/queens/{queen}`

`PATCH api/queens/{queen}`


<!-- END_20c827dbbb9d7175c20551b24a9b21bc -->

<!-- START_54031759896fd0124d491e1daa0637da -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/queens/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/queens/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/queens/{queen}`


<!-- END_54031759896fd0124d491e1daa0637da -->

#Api\ResearchController


<!-- START_5f81999d6a12d44ae368b63e6fae439d -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/research" 
```

```javascript
const url = new URL("https://test.beep.nl/api/research");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/research`


<!-- END_5f81999d6a12d44ae368b63e6fae439d -->

<!-- START_5ba9f23a9188b99ee63b3014619f551c -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/research/1/add_consent" 
```

```javascript
const url = new URL("https://test.beep.nl/api/research/1/add_consent");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/research/{id}/add_consent`


<!-- END_5ba9f23a9188b99ee63b3014619f551c -->

<!-- START_e41043cf612829839943f89dda227c03 -->
## api/research/{id}/remove_consent
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/research/1/remove_consent" 
```

```javascript
const url = new URL("https://test.beep.nl/api/research/1/remove_consent");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/research/{id}/remove_consent`


<!-- END_e41043cf612829839943f89dda227c03 -->

#Api\SensorDefinitionController


<!-- START_3b98cb44d6fe4407585509ca2f891fda -->
## api/sensordefinition GET
Display a listing of the resource.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/sensordefinition" \
    -H "Content-Type: application/json" \
    -d '{"device_id":14,"device_hardware_id":"voluptas"}'

```

```javascript
const url = new URL("https://test.beep.nl/api/sensordefinition");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "device_id": 14,
    "device_hardware_id": "voluptas"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/sensordefinition`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    device_id | integer |  optional  | Device that the definition value belongs to
    device_hardware_id | string |  required  | Device that the definition values belong to

<!-- END_3b98cb44d6fe4407585509ca2f891fda -->

<!-- START_f6f20cfc18b5c347368408d17f1981d8 -->
## api/sensordefinition POST
Store a newly created resource in storage.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/sensordefinition" \
    -H "Content-Type: application/json" \
    -d '{"name":"sed","inside":false,"offset":50960606.897084,"multiplier":50.193383,"input_measurement_id":5,"input_measurement_abbreviation":"w_v","output_measurement_id":6,"output_measurement_abbreviation":"t_i","device_id":15,"device_hardware_id":"consequatur"}'

```

```javascript
const url = new URL("https://test.beep.nl/api/sensordefinition");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "name": "sed",
    "inside": false,
    "offset": 50960606.897084,
    "multiplier": 50.193383,
    "input_measurement_id": 5,
    "input_measurement_abbreviation": "w_v",
    "output_measurement_id": 6,
    "output_measurement_abbreviation": "t_i",
    "device_id": 15,
    "device_hardware_id": "consequatur"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/sensordefinition`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    name | string |  optional  | Name of the sensorinstance (e.g. temperature frame 1)
    inside | boolean |  optional  | True is measured inside, false if measured outside
    offset | float |  optional  | Measurement value that defines 0
    multiplier | float |  optional  | Amount of units (calibration figure) per delta Measurement value to multiply withy (value - offset)
    input_measurement_id | integer |  optional  | Measurement that represents the input Measurement value (e.g. 5, 3).
    input_measurement_abbreviation | string |  optional  | Abbreviation of the Measurement that represents the input value (e.g. w_v, or t_i).
    output_measurement_id | integer |  optional  | Measurement that represents the output Measurement value (e.g. 6, 3).
    output_measurement_abbreviation | string |  optional  | Abbreviation of the Measurement that represents the output (calculated with (raw_value - offset) * multiplier) value (e.g. weight_kg, or t_i),
    device_id | integer |  optional  | Device that the Measurement value belongs to
    device_hardware_id | string |  required  | Device that the Measurement values belong to

<!-- END_f6f20cfc18b5c347368408d17f1981d8 -->

<!-- START_ff9674949ad011c894c60a5928baa7be -->
## api/sensordefinition/{id} GET
Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/sensordefinition/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensordefinition/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/sensordefinition/{sensordefinition}`


<!-- END_ff9674949ad011c894c60a5928baa7be -->

<!-- START_4a67b5f3dbe74a8ad47cfe8979c6207b -->
## api/sensordefinition PATCH
Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/sensordefinition/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensordefinition/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/sensordefinition/{sensordefinition}`

`PATCH api/sensordefinition/{sensordefinition}`


<!-- END_4a67b5f3dbe74a8ad47cfe8979c6207b -->

<!-- START_088c023a2cbee7a7aa87c3abaece68ea -->
## api/sensordefinition DELETE
Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/sensordefinition/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensordefinition/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/sensordefinition/{sensordefinition}`


<!-- END_088c023a2cbee7a7aa87c3abaece68ea -->

#Api\SettingController


<!-- START_1e1aaba3a713ac3ce04a89d5f4ad0f2e -->
## api/settings
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/settings" 
```

```javascript
const url = new URL("https://test.beep.nl/api/settings");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/settings`


<!-- END_1e1aaba3a713ac3ce04a89d5f4ad0f2e -->

<!-- START_10633908636252dc838d3a5bd392f07c -->
## api/settings
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/settings" 
```

```javascript
const url = new URL("https://test.beep.nl/api/settings");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/settings`


<!-- END_10633908636252dc838d3a5bd392f07c -->

#Api\TaxonomyController


<!-- START_f0dc9906634dd344e86ab96b3f489333 -->
## api/taxonomy/lists
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/taxonomy/lists" 
```

```javascript
const url = new URL("https://test.beep.nl/api/taxonomy/lists");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/taxonomy/lists`


<!-- END_f0dc9906634dd344e86ab96b3f489333 -->

<!-- START_39819739c1fe97abb680ee9f8137ec84 -->
## api/taxonomy/taxonomy
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/taxonomy/taxonomy" 
```

```javascript
const url = new URL("https://test.beep.nl/api/taxonomy/taxonomy");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/taxonomy/taxonomy`


<!-- END_39819739c1fe97abb680ee9f8137ec84 -->

#Api\UserController


APIs for managing users
<!-- START_d7b7952e7fdddc07c978c9bdaf757acf -->
## api/register
Registers a new user and sends an e-mail verification request on succesful save

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/register" \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"testtest","policy_accepted":"beep_terms_2018_05_25_avg_v1"}'

```

```javascript
const url = new URL("https://test.beep.nl/api/register");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "email": "test@test.com",
    "password": "testtest",
    "policy_accepted": "beep_terms_2018_05_25_avg_v1"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/register`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    email | string |  required  | Email address of the user.
    password | string |  required  | Password of the user.
    policy_accepted | string |  required  | Name of the privacy policy that has been accepted by the user by ticking the accept terms box.

<!-- END_d7b7952e7fdddc07c978c9bdaf757acf -->

<!-- START_c3fa189a6c95ca36ad6ac4791a873d23 -->
## api/login
Login via login form

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/login" \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"testtest"}'

```

```javascript
const url = new URL("https://test.beep.nl/api/login");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "email": "test@test.com",
    "password": "testtest"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "id": 1317,
    "name": "test@test.com",
    "email": "test@test.com",
    "avatar": "default.jpg",
    "api_token": "1snu2aRRiwQNl2Tul567hLF0XpKuZO8hqkgXU4GvjzZ3f3pOCiDPFbBDea7W",
    "created_at": "2018-12-30 23:57:35",
    "updated_at": "2020-01-09 16:31:32",
    "last_login": "2020-01-09 16:31:32",
    "policy_accepted": "beep_terms_2018_05_25_avg_v1",
    "email_verified_at": "2018-05-25 00:00:00"
}
```

### HTTP Request
`POST api/login`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    email | string |  required  | Email address of the user.
    password | string |  required  | Password of the user.

<!-- END_c3fa189a6c95ca36ad6ac4791a873d23 -->

<!-- START_ecb0fb5f68c755d234f5903658956ead -->
## api/user/reminder
Send password reset link
responses: invalid_user, reminder_sent, invalid_password, invalid_token, password_reset

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/user/reminder" \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com"}'

```

```javascript
const url = new URL("https://test.beep.nl/api/user/reminder");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "email": "test@test.com"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "message": "reminder_sent"
}
```

### HTTP Request
`POST api/user/reminder`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    email | string |  required  | Email address of the user.

<!-- END_ecb0fb5f68c755d234f5903658956ead -->

<!-- START_5a5b59444cee7eb79d151113de4eec9c -->
## api/user/reset
Reset the user passowrd with a reset link
responses: INVALID_USER, RESET_LINK_SENT, INVALID_PASSWORD, INVALID_TOKEN, PASSWORD_RESET

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/user/reset" \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"testtest","password_confirm":"testtest","token":"z8iQafmgP1"}'

```

```javascript
const url = new URL("https://test.beep.nl/api/user/reset");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "email": "test@test.com",
    "password": "testtest",
    "password_confirm": "testtest",
    "token": "z8iQafmgP1"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/user/reset`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    email | string |  required  | Email address of the user.
    password | string |  required  | Password of the user.
    password_confirm | string |  required  | Password confirmation of the user.
    token | string |  required  | Token sent in the reminder e-mail to the email address of the user.

<!-- END_5a5b59444cee7eb79d151113de4eec9c -->

<!-- START_4a6a89e9e0eaea9c72ceea57315f2c42 -->
## api/authenticate
Authorize a user and login with an api_token. Used for persistent login in webapp.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
Header parameter with Bearer [api_token] from the user object. Example: Bearer 1snu2aRRiwQNl2Tul567hLF0XpKuZO8hqkgXU4GvjzZ3f3pOCiDPFbBDea7W

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/authenticate" 
```

```javascript
const url = new URL("https://test.beep.nl/api/authenticate");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "id": 1317,
    "name": "test@test.com",
    "email": "test@test.com",
    "avatar": "default.jpg",
    "api_token": "1snu2aRRiwQNl2Tul567hLF0XpKuZO8hqkgXU4GvjzZ3f3pOCiDPFbBDea7W",
    "created_at": "2018-12-30 23:57:35",
    "updated_at": "2020-01-09 16:31:32",
    "last_login": "2020-01-09 16:31:32",
    "policy_accepted": "beep_terms_2018_05_25_avg_v1",
    "email_verified_at": "2018-05-25 00:00:00"
}
```

### HTTP Request
`POST api/authenticate`


<!-- END_4a6a89e9e0eaea9c72ceea57315f2c42 -->

<!-- START_43e8ba205ffd3cbca826e9ab0a6f5af1 -->
## api/user DELETE
Destroy the logged in user and all its data in the database

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/user" 
```

```javascript
const url = new URL("https://test.beep.nl/api/user");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/user`


<!-- END_43e8ba205ffd3cbca826e9ab0a6f5af1 -->

<!-- START_e75f2f63a5a2351c4f4d83bc65cefcf8 -->
## api/user PATCH
Edit the user details

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X PATCH "https://test.beep.nl/api/user" \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"testtest","password_confirm":"testtest","policy_accepted":"beep_terms_2018_05_25_avg_v1"}'

```

```javascript
const url = new URL("https://test.beep.nl/api/user");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "email": "test@test.com",
    "password": "testtest",
    "password_confirm": "testtest",
    "policy_accepted": "beep_terms_2018_05_25_avg_v1"
}

fetch(url, {
    method: "PATCH",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PATCH api/user`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    email | string |  required  | Email address of the user.
    password | string |  required  | Password of the user.
    password_confirm | string |  required  | Password confirmation of the user.
    policy_accepted | string |  optional  | Name of the privacy policy that has been accepted by the user by ticking the accept terms box.

<!-- END_e75f2f63a5a2351c4f4d83bc65cefcf8 -->

#Api\VerificationController

User verification functions
<!-- START_2d698b6d6bc7441f9c1a9cf11aec4059 -->
## Show the email verification notice.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/email/verify" 
```

```javascript
const url = new URL("https://test.beep.nl/api/email/verify");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response:

```json
null
```

### HTTP Request
`GET api/email/verify`


<!-- END_2d698b6d6bc7441f9c1a9cf11aec4059 -->

<!-- START_d83e982c7c8172810ed08568400567aa -->
## Mark the authenticated user&#039;s email address as verified.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/email/verify/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/email/verify/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (403):

```json
{
    "message": "Invalid signature."
}
```

### HTTP Request
`GET api/email/verify/{id}`


<!-- END_d83e982c7c8172810ed08568400567aa -->

<!-- START_007d2c80092c02b58e6bfecd510a3282 -->
## Resend the email verification notification.

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/email/resend" 
```

```javascript
const url = new URL("https://test.beep.nl/api/email/resend");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/email/resend`


<!-- END_007d2c80092c02b58e6bfecd510a3282 -->

#general


<!-- START_f2ce4d0ca8bf3878a3fd61cbc4528bdd -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/weather" 
```

```javascript
const url = new URL("https://test.beep.nl/api/weather");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/weather`


<!-- END_f2ce4d0ca8bf3878a3fd61cbc4528bdd -->


