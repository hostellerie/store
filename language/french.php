<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | french.php                                                               |
// |                                                                          |
// | French language strings and Store configuration labels.                  |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software              |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA                |
// | 02111-1307, USA.                                                         |
// |                                                                          |
// +---------------------------------------------------------------------------+

$LANG_configsections['store'] = array('label' => 'Store', 'title' => 'Configuration du plugin Store');
$LANG_confignames['store'] = array(
    'hide_menu' => 'Masquer Store dans le menu principal',
    'currency' => 'Devise par défaut (code ISO 4217)',
    'currency_decimals' => 'Nombre de décimales pour les montants',
    'products_per_page' => 'Produits par page',
    'show_stock' => 'Afficher le stock sur les pages publiques',
    'manual_payment_enabled' => 'Activer le paiement manuel / virement',
    'manual_payment_label' => 'Libellé du moyen de paiement manuel',
    'manual_payment_instructions' => 'Instructions de paiement manuel affichées après la commande',
    'tax_enabled' => 'Activer le calcul des taxes',
    'prices_include_tax' => 'Les prix du catalogue incluent les taxes',
    'tax_basis' => 'Base de calcul des taxes',
    'tax_rounding' => 'Méthode d’arrondi des taxes',
    'shipping_enabled' => 'Activer les méthodes de livraison',
    'default_country_code' => 'Code pays par défaut (ISO 3166-1 alpha-2)',
    'weight_unit' => 'Unité de poids',
    'dimension_unit' => 'Unité de dimensions',
    'layout_blocks' => 'Colonnes Geeklog affichées sur les pages Store'
);
$LANG_configsubgroups['store'] = array('sg_main' => 'Paramètres Store');
$LANG_fs['store'] = array(
    'fs_main' => 'Paramètres généraux',
    'fs_catalog' => 'Catalogue',
    'fs_checkout' => 'Commande et paiement',
    'fs_commerce' => 'Taxes et livraison',
    'fs_display' => 'Affichage public'
);
$LANG_tab['store'] = array(
    'tab_main' => 'Général',
    'tab_catalog' => 'Catalogue',
    'tab_checkout' => 'Commande et paiement',
    'tab_commerce' => 'Taxes et livraison',
    'tab_display' => 'Affichage'
);
$LANG_configselects['store'] = array(
    0 => array('Oui' => 1, 'Non' => 0),
    1 => array(
        'Aucune colonne (pleine largeur)' => 'none',
        'Colonne gauche uniquement' => 'left',
        'Colonne droite uniquement' => 'right',
        'Colonnes gauche et droite' => 'both'
    )
);

$LANG_STORE = array(
    'plugin_name' => 'Store',
    'menu_store' => 'Boutique',
    'catalog_title' => 'Boutique',
    'catalog_empty' => 'Aucun produit n’est encore disponible.',
    'product_not_found' => 'Produit introuvable.',
    'price' => 'Prix',
    'stock' => 'Stock',
    'weight' => 'Poids',
    'dimensions' => 'Dimensions',
    'physical_details' => 'Caractéristiques physiques',
    'stock_physical_only' => 'Le stock s’applique uniquement aux produits physiques. Les produits numériques ne sont pas limités par le stock.',
    'in_stock' => 'en stock',
    'out_of_stock' => 'Rupture de stock',
    'sku' => 'Référence',
    'details' => 'Voir le produit',
    'back_catalog' => 'Retour à la boutique',
    'admin_title' => 'Administration de Store',
    'admin_intro' => 'Cette première version de Store permet de créer et publier un catalogue simple de produits.',
    'add_product' => 'Ajouter un produit',
    'edit_product' => 'Modifier le produit',
    'edit_product_help' => 'Enregistrez les modifications sans quitter ce produit, puis gérez sa galerie ci-dessous.',
    'product_information' => 'Informations du produit',
    'sales_settings' => 'Vente et disponibilité',
    'products' => 'Produits',
    'name' => 'Nom',
    'slug' => 'Identifiant URL',
    'short_description' => 'Description courte',
    'description' => 'Description',
    'currency' => 'Devise',
    'product_type' => 'Type de produit',
    'physical' => 'Physique',
    'digital' => 'Numérique',
    'active' => 'Publié',
    'yes' => 'Oui',
    'no' => 'Non',
    'save' => 'Enregistrer',
    'cancel' => 'Annuler',
    'edit' => 'Modifier',
    'delete' => 'Supprimer',
    'confirm_delete' => 'Supprimer ce produit ?',
    'saved' => 'Le produit a été enregistré.',
    'deleted' => 'Le produit a été supprimé.',
    'invalid_token' => 'Le jeton de sécurité a expiré. Veuillez réessayer.',
    'name_required' => 'Le nom du produit est obligatoire.',
    'slug_exists' => 'Cet identifiant URL est déjà utilisé par un autre produit.',
    'categories' => 'Catégories',
    'category' => 'Catégorie',
    'no_category' => 'Sans catégorie',
    'add_category' => 'Ajouter une catégorie',
    'category_saved' => 'La catégorie a été enregistrée.',
    'category_name_required' => 'Le nom de la catégorie est obligatoire.',
    'image_url' => 'URL de l’image principale',
    'image_url_help' => 'Choisissez une image avec le gestionnaire de fichiers Geeklog ou saisissez une URL http(s) ou un chemin relatif au site.',
    'choose_image' => 'Choisir une image',
    'choose_images' => 'Choisir des images',
    'cart' => 'Panier',
    'add_to_cart' => 'Ajouter au panier',
    'cart_empty' => 'Votre panier est vide.',
    'quantity' => 'Quantité',
    'total' => 'Total',
    'update_cart' => 'Mettre à jour le panier',
    'continue_shopping' => 'Continuer mes achats',
    'added_to_cart' => 'Le produit a été ajouté au panier.',
    'view_product' => 'Voir la fiche',
    'product_images' => 'Images du produit',
    'add_image' => 'Ajouter une image',
    'add_selected_images' => 'Ajouter les images sélectionnées',
    'selected_images' => 'Images à ajouter',
    'selected_images_help' => 'Sélectionnez plusieurs images successivement dans le gestionnaire de fichiers Geeklog ou saisissez une URL d’image par ligne.',
    'multiple_images_help' => 'Le gestionnaire de fichiers reste disponible pendant vos sélections. Chaque image choisie est ajoutée à cette liste ; enregistrez-les ensemble lorsque vous avez terminé.',
    'first_image_primary' => 'Utiliser la première image ajoutée comme image principale',
    'make_primary' => 'Définir comme principale',
    'primary_image' => 'Image principale',
    'confirm_delete_image' => 'Supprimer cette image ?',
    'image_added' => 'L’image a été ajoutée.',
    'images_added' => '%d image(s) ajoutée(s).',
    'image_deleted' => 'L’image a été supprimée.',
    'primary_updated' => 'L’image principale a été mise à jour.',
    'invalid_image_url' => 'Choisissez ou saisissez une URL d’image valide.',
    'configuration' => 'Configuration',
    'back_to_products' => 'Retour aux produits',
    'save_and_close' => 'Enregistrer et fermer',
    'categories_help' => 'Organisez le catalogue avec des catégories de produits simples.',
    'category_name' => 'Nom de la catégorie',
    'no_product_image' => 'Aucune image pour ce produit',
    'all_products' => 'Tous les produits',
    'checkout' => 'Commander',
    'checkout_intro' => 'Vérifiez votre panier puis renseignez les coordonnées nécessaires à la création de la commande.',
    'customer_details' => 'Coordonnées client',
    'customer_name' => 'Nom complet',
    'customer_email' => 'Adresse email',
    'address1' => 'Adresse',
    'address2' => 'Complément d’adresse',
    'postal_code' => 'Code postal',
    'city' => 'Ville',
    'country' => 'Pays',
    'place_order' => 'Créer la commande',
    'order_created' => 'Votre commande %s a été créée.',
    'order' => 'Commande',
    'orders' => 'Commandes',
    'my_orders' => 'Mes commandes',
    'no_orders' => 'Aucune commande disponible.',
    'order_number' => 'Numéro de commande',
    'order_date' => 'Date',
    'order_status' => 'Statut',
    'order_total' => 'Total',
    'order_details' => 'Détail de la commande',
    'customer' => 'Client',
    'status_pending' => 'En attente',
    'status_paid' => 'Payée',
    'status_processing' => 'En traitement',
    'status_completed' => 'Terminée',
    'status_cancelled' => 'Annulée',
    'update_status' => 'Modifier le statut',
    'status_updated' => 'Le statut de la commande a été mis à jour.',
    'status_update_failed' => 'Le statut n’a pas pu être modifié. Vérifiez notamment le stock avant de réactiver une commande annulée.',
    'checkout_customer_error' => 'Renseignez un nom et une adresse email valides.',
    'checkout_address_error' => 'Renseignez l’adresse de livraison pour les produits physiques.',
    'checkout_stock_error' => 'Une ou plusieurs quantités demandées ne sont plus disponibles. Mettez le panier à jour.',
    'checkout_currency_error' => 'Une même commande ne peut pas encore contenir des produits dans plusieurs devises.',
    'checkout_database_error' => 'La commande n’a pas pu être créée. Veuillez réessayer.',
    'login_for_history' => 'Connectez-vous pour consulter l’historique de vos commandes.',
    'payment' => 'Paiement',
    'payment_method' => 'Moyen de paiement',
    'payment_manual' => 'Virement / paiement manuel',
    'payment_status' => 'Statut du paiement',
    'payment_reference' => 'Référence du paiement',
    'update_payment' => 'Mettre à jour le paiement',
    'payment_updated' => 'Le statut du paiement a été mis à jour.',
    'payment_update_failed' => 'Le statut du paiement n’a pas pu être mis à jour.',
    'payment_status_pending' => 'En attente de paiement',
    'payment_status_paid' => 'Payé',
    'payment_status_failed' => 'Échec',
    'payment_status_cancelled' => 'Annulé',
    'payment_status_refunded' => 'Remboursé',
    'checkout_payment_error' => 'Aucun moyen de paiement valide n’est actuellement disponible.',
    'pos' => 'Caisse',
    'pos_title' => 'Caisse',
    'pos_intro' => 'Enregistrez une vente sur place avec le même catalogue, le même stock et les mêmes commandes que la boutique en ligne.',
    'pos_search' => 'Rechercher un produit...',
    'pos_ticket' => 'Vente en cours',
    'pos_empty' => 'Aucun produit dans cette vente.',
    'pos_payment' => 'Mode de règlement',
    'pos_payment_cash' => 'Espèces',
    'pos_payment_card' => 'Carte bancaire',
    'pos_payment_cheque' => 'Chèque',
    'pos_payment_transfer' => 'Virement',
    'pos_payment_other' => 'Autre',
    'pos_customer_optional' => 'Client (facultatif)',
    'pos_walk_in_customer' => 'Client comptoir',
    'pos_validate' => 'Valider la vente',
    'pos_sale_created' => 'La vente %s a été créée.',
    'pos_sale_error' => "La vente n'a pas pu être créée.",
    'pos_product_error' => "Un des produits sélectionnés n'est plus disponible.",
    'pos_receipt' => 'Reçu',
    'pos_print_receipt' => 'Imprimer le reçu',
    'pos_new_sale' => 'Nouvelle vente',
    'pos_source' => 'Origine',
    'source_online' => 'En ligne',
    'source_pos' => 'Caisse',
    'pos_stock' => 'Stock : %d',
    'pos_digital' => 'Produit numérique',
    'pos_no_image' => 'Aucune image',
    'pos_sku' => 'Réf. : %s',
    'roadmap' => 'Roadmap',
    'roadmap_title' => 'Feuille de route de Store',
    'roadmap_intro' => 'Feuille de route technique évolutive pour le développement international de Geeklog Store.',
    'roadmap_file_missing' => 'Le fichier de feuille de route de Store est introuvable.',
    'roadmap_updated' => 'Feuille de route intégrée à Store %s.',
    'version' => 'Version 0.7.0'
);
