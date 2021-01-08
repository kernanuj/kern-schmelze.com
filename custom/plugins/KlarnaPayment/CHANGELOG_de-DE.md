# 1.3.7
- Behebung von Fehlern in der Pluginkonfiguration
- Bitte beachten Sie: Ein Fehlverhalten bei vererbten Konfigurationswerten ist bekannt und in der Dokumentation näher beschrieben.

# 1.3.6
- Optimierung der Fehlerbehandlung bei der Verwendung von Instant Shopping
- Korrektur der fehlenden Bezeichnungen in der Plugin-Konfiguration
- Hinweis in der Dokumentation (https://klarna.pluginwerk.de/de/1-3/index.html#h-uebersetzung-der-zahlarten) zur Übersetzung von Zahlungsarten ergänzt.

# 1.3.5
- Ergänzung eines Standardnamens für die Zahlungsarten für die Installation unter einer anderen Systemsprache

# 1.3.4
- Korrektur der Handhabung von Gutscheinen im Warenkorb

# 1.3.3
- Korrektur des automatischen Packens des ZIP-Archiv um kompilierte Dateien wieder zu ergänzen
- Korrektur der Verwendung einer falschen Exception-Klasse

# 1.3.2
- Korrektur der Darstellung vom Instant Shopping Button
- Korrektur eines Fehlers bei der Aktualisierung der Bestellung wenn die Bestellung bereits eingezogen wurde
- Optimierung der Performance im Checkout und beim Aktualisieren der Bestellung

# 1.3.1
- Korrektur der Selektierung einer Bestellung aus den Suchergebnissen

# 1.3.0
- Sprache und Währung zur Verfügbarkeitsregel von Klarna Zahlarten hinzugefügt
- Übermittlung von Informationen zur Versandverfolgung an das Klarna Händlerportal  
- Korrektur der Installation, wenn eine Sprache nicht gefunden werden konnte
- Korrektur von Instant Shopping für Gäste bei konfigurierten erforderlichen Einstellungen für die Registrierung
- Optimierung der Validierung von API Zugangsdaten

# 1.2.1
- Korrektur der Anzeige von Lieferinformationen auf Produktdetailseiten
- Korrektur von Bestellaktualisierungen in der Administration mit anderen Zahlungsmethoden

# 1.2.0
- Kompatibilität zu Shopware 6.2 hergestellt

# 1.1.0
- Implementierung von Klarna Instant Shopping
- Unterstützung von reinen Nettopreisen hinzugefügt (ab Shopware 6.2.0)

# 1.0.4
- Korrektur des Bestellabschluss-Buttons für andere Zahlungsmethoden

# 1.0.3
- Korrektur des Namens der Rechnungsadresse in der Klarna Payment Session

# 1.0.2
- Kombinierte Pay Now-Zahlungsart und Kreditkarte ergänzt
- Bestellanpassungen in der Administration werden durch den Versions-Manager nun vor dem Speichern mit Klarna abgeglichen
- Anpassung an den Adressübergaben
- Zahlungskategorien können nun in der Pluginkonfiguration deaktiviert werden
- Abgleich von Bestellungsanpassungen während dem Bestellprozess werden nun verhindert, solange die Bestellung noch nicht bei Klarna angelegt wurde

# 1.0.1
- Rundungsfehler bei Klarna-Aufrufen behoben

# 1.0.0
- Erste Version der Klarna Payment Integration für Shopware 6.1
