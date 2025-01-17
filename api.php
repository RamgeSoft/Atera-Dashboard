<?php
header("Content-Type: application/json");

// API-Schlüssel eintragen
$apiKey = "DEIN-ATERA-API-KEY"; // Ersetze mit deinem echten API-Key

$customerId = isset($_GET["customer"]) ? $_GET["customer"] : "";
$page = isset($_GET["page"]) ? intval($_GET["page"]) : 1;
$type = isset($_GET["type"]) ? $_GET["type"] : "tickets";
$perPage = 5;
$maxPages = 10; // Begrenzung auf maximal 10 API-Seiten, um Endlosschleifen zu vermeiden

// API-Endpunkte
$apiEndpoints = [
    "tickets" => "https://app.atera.com/api/v3/tickets?ticketStatus=Open",
    "alerts" => "https://app.atera.com/api/v3/alerts",
    "customers" => "https://app.atera.com/api/v3/customers"
];

$results = [];

foreach ($apiEndpoints as $key => $url) {
    if ($customerId && ($key == "tickets" || $key == "alerts")) {
        $url .= "&customerId=" . urlencode($customerId);
    }

    $allItems = [];
    $currentPage = 0;

    do {
        $currentPage++;
        if ($currentPage > $maxPages) {
            break; // Sicherheitsschleife, um nicht endlos zu laden
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: application/json",
            "X-API-KEY: $apiKey"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (!$data || !isset($data["items"])) {
            break; // Falls fehlerhafte Antwort kommt, abbrechen
        }

        $items = $data["items"];
        $allItems = array_merge($allItems, $items);
        $url = isset($data["nextLink"]) ? $data["nextLink"] : null; // Prüfen, ob es eine nächste Seite gibt

    } while ($url); // Solange weitere Seiten existieren

    // Filter für Alerts (nur relevante anzeigen & nach CustomerID filtern)
    if ($key == "alerts") {
        $filteredAlerts = array_filter($allItems, function ($alert) use ($customerId) {
            return !$alert["Archived"] 
                && in_array($alert["Severity"], ["Critical", "Warning"]) 
                && ($customerId == "" || $alert["CustomerID"] == intval($customerId));
        });

        // Pagination auf gefilterte Alerts anwenden
        $totalAlerts = count($filteredAlerts);
        $pagedAlerts = array_slice(array_values($filteredAlerts), ($page - 1) * $perPage, $perPage);

        $results["alertsTotal"] = $totalAlerts;
        $results["alerts"] = $pagedAlerts;
    } 
    // Filter für Tickets (keine mit "EndUserLastName" = "ALERT:")
    elseif ($key == "tickets") {
        $filteredTickets = array_filter($allItems, function ($ticket) {
            return strpos($ticket["EndUserLastName"], "ALERT:") !== 0;
        });

        $totalTickets = count($filteredTickets);
        $pagedTickets = array_slice(array_values($filteredTickets), ($page - 1) * $perPage, $perPage);

        $results["ticketsTotal"] = $totalTickets;
        $results["latestTickets"] = $pagedTickets;
    } 
    else {
        $results[$key] = $allItems;
    }
}

echo json_encode($results);
?>
