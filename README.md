# **Atera Dashboard** 🚀  
Ein modernes, responsives **Dashboard für Atera**, das **Tickets und Alerts** übersichtlich darstellt.  
Unterstützt **Pagination** und **Kundenfilterung**.  

![Atera Dashboard Screenshot](https://via.placeholder.com/1200x600.png?text=Atera+Dashboard)  

---

## **📌 Features**  
✅ **Live-Übersicht** über offene Tickets und Alerts  
✅ **Filter nach Kunden (Customer)**  
✅ **Pagination für Tickets & Alerts**  
✅ **Responsive UI mit TailwindCSS**  

---

## **📂 Installation**  

### **🔧 Voraussetzungen**  
- **PHP 8+** (für die API-Abfragen)  
- **Webserver** (z. B. Apache, Nginx oder PHP-Built-in Server)  
- **Git** (optional für Repository-Verwaltung)  

### **📥 Repository klonen**  
```
git clone https://github.com/dein-username/atera-dashboard.git
cd atera-dashboard
```

## **🛠 Konfiguration**  

### **🌐 API-Zugangsdaten setzen**  
Öffne die Datei **`api.php`** und trage deinen **Atera API Key** ein:
```
$apiKey = "DEIN-ATERA-API-KEY";
```

Falls du **eine andere API-Version** nutzen willst, passe den Endpunkt in **`api.php`** an:
```
$apiEndpoints = [
    "tickets" => "https://app.atera.com/api/v3/tickets",
    "alerts" => "https://app.atera.com/api/v3/alerts",
    "customers" => "https://app.atera.com/api/v3/customers"
];
```

---

## **🔄 Automatische Datenaktualisierung**  
- Tickets & Alerts werden **alle 30 Sekunden aktualisiert**  
- Pagination funktioniert für größere Datensätze  

---

## **📩 Kontakt & Support**  
📧 **E-Mail:** helpdesk@ramgesoft.de  
🐙 **GitHub Issues:** [Issue Tracker](https://github.com/ramgesoft/atera-dashboard/issues)  
🌎 **Website:** [RamgeSoft](https://ramgesoft.com)  

---

**🚀 Viel Spaß mit dem Atera Dashboard! Falls du Fragen oder Vorschläge hast, erstelle einfach ein Issue.** 😊  
