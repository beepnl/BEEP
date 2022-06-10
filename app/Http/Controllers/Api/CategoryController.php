<?php

namespace App\Http\Controllers\Api;

use App\Category;
use App\CategoryInput;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Api\CategoryController
 * All categories in the categorization tree used for hive inspections
 * Only used to get listing (index) or one category (show)
 * @authenticated
 */
class CategoryController extends Controller
{

    /**
     * api/categoryinputs
     * List of all available input types of the inspection categories
     * @authenticated
     * @response[
     {
        "id": 1,
        "name": "List",
        "type": "list",
        "min": null,
        "max": null,
        "decimals": null
    },
    {
        "id": 2,
        "name": "List item",
        "type": "list_item",
        "min": null,
        "max": null,
        "decimals": null
    },
    {
        "id": 3,
        "name": "Boolean (yes = green)",
        "type": "boolean",
        "min": null,
        "max": null,
        "decimals": null
    }
    ]
     */
    public function inputs(Request $request)
    {
        //die($category);
        return response()->json(CategoryInput::all());
    }

    /**
     * api/categories/{id}
     * Display the specified category.
     * @authenticated
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Category $category)
    {
        //die($category);
        return response()->json($category);
    }

    /**
     * api/categories
     * Display a listing of the inspection categories.
     * @authenticated
     * @return \Illuminate\Http\Response
     * @response[
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
                    "es": "Compañía/Empresa",
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
     */
    public function index(Request $request)
    {
        return response()->json(Category::whereIsRoot()->with('children')->get());
    }
}
