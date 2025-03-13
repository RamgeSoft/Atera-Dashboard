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
    "tickets" => "https://app.atera.com/api/v3/tickets",
    "alerts" => "https://app.atera.com/api/v3/alerts",
    "customers" => "https://app.atera.com/api/v3/customers"
];

// Array mit den gewünschten Ticket-Status
$ticketStatuses = ['Open', 'Pending', 'Waiting for Technician', 'Waiting for Customer']; // Hier kannst du weitere Stati hinzufügen

$results = [];

if ($type == "tickets") {
    $allTickets = [];
    
    // Für jeden Ticket-Status wird ein separater Request ausgeführt
    foreach ($ticketStatuses as $status) {
        $url = $apiEndpoints["tickets"] . "?ticketStatus=" . urlencode($status);
        if ($customerId) {
            $url .= "&customerId=" . urlencode($customerId);
        }
        
        $currentPage = 0;
        do {
            $currentPage++;
            if ($currentPage > $maxPages) {
                break; // Sicherheitsschleife
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
                break; // Abbruch bei fehlerhafter Antwort
            }
    
            $allTickets = array_merge($allTickets, $data["items"]);
            $url = isset($data["nextLink"]) ? $data["nextLink"] : null;
    
        } while ($url); // solange weitere Seiten existieren
    }
    
    // Filter: Tickets ausschließen, deren "EndUserLastName" mit "ALERT:" beginnt
    $filteredTickets = array_filter($allTickets, function ($ticket) {
        return strpos($ticket["EndUserLastName"], "ALERT:") !== 0;
    });
    
    // Gesamte gefilterte Tickets
    $totalTickets = count($filteredTickets);
    
    // Berechnung der Totals für "Open" und "Pending" Tickets
    $openTicketsCount = count(array_filter($filteredTickets, function ($ticket) {
        return isset($ticket["ticketStatus"]) && $ticket["ticketStatus"] === "Open";
    }));
    
    $pendingTicketsCount = count(array_filter($filteredTickets, function ($ticket) {
        return isset($ticket["ticketStatus"]) && $ticket["ticketStatus"] === "Pending";
    }));
    
    // Pagination auf alle gefilterten Tickets anwenden
    $pagedTickets = array_slice(array_values($filteredTickets), ($page - 1) * $perPage, $perPage);
    
    // "ticketsTotal" als Array mit separaten Zählungen zurückgeben
    $results["ticketsTotal"] = [
         "openTickets"    => $openTicketsCount,
         "pendingTickets" => $pendingTicketsCount,
         "all"            => $totalTickets // Optional, falls der Gesamtwert benötigt wird
    ];
    $results["latestTickets"] = $pagedTickets;
    
} else {
    // Verarbeitung für "alerts" und "customers"
    foreach ($apiEndpoints as $key => $url) {
        if ($customerId && ($key == "tickets" || $key == "alerts")) {
            $url .= "&customerId=" . urlencode($customerId);
        }
    
        $allItems = [];
        $currentPage = 0;
    
        do {
            $currentPage++;
            if ($currentPage > $maxPages) {
                break;
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
                break;
            }
    
            $items = $data["items"];
            $allItems = array_merge($allItems, $items);
            $url = isset($data["nextLink"]) ? $data["nextLink"] : null;
    
        } while ($url);
    
        if ($key == "alerts") {
            $filteredAlerts = array_filter($allItems, function ($alert) use ($customerId) {
                return !$alert["Archived"] 
                    && in_array($alert["Severity"], ["Critical", "Warning"]) 
                    && ($customerId == "" || $alert["CustomerID"] == intval($customerId));
            });
    
            $totalAlerts = count($filteredAlerts);
            $pagedAlerts = array_slice(array_values($filteredAlerts), ($page - 1) * $perPage, $perPage);
    
            $results["alertsTotal"] = $totalAlerts;
            $results["alerts"] = $pagedAlerts;
        } else {
            $results[$key] = $allItems;
        }
    }
}

echo json_encode($results);
?>
