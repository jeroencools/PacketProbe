// assets/js/protocols.js
// Protocoluitleg met metaforen, samenvattingen en NL Wikipedia links
window.protocolExplanations = {
  'HTTP': {
    metaphor: 'HTTP is als een ober die je bestelling opneemt en je eten vanuit de keuken brengt.',
    explanation: [
      'HTTP staat voor HyperText Transfer Protocol en is de basis voor gegevensoverdracht op het wereldwijde web.',
      'Het definieert hoe berichten worden opgemaakt en verzonden, en hoe servers en browsers daarop reageren.',
      'Wanneer je een website bezoekt, stuurt je browser een HTTP-verzoek naar de server, die vervolgens de gevraagde pagina terugstuurt.',
      'HTTP is stateless: elke aanvraag is op zichzelf staand en de server onthoudt geen gegevens tussen aanvragen.',
      'Het gebruikt meestal poort 80 en is niet versleuteld, wat het ongeschikt maakt voor gevoelige informatie.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Hypertext_Transfer_Protocol'
  },
  'HTTPS': {
    metaphor: 'HTTPS is als je bestelling naar de keuken sturen in een afgesloten doos die alleen de keuken kan openen.',
    explanation: [
      'HTTPS staat voor HyperText Transfer Protocol Secure en is de beveiligde versie van HTTP.',
      'Het gebruikt versleuteling (meestal TLS) om gegevens tussen browser en server te beschermen.',
      'Zo blijft gevoelige informatie zoals wachtwoorden en betaalgegevens veilig tegen afluisteren.',
      'HTTPS is onmisbaar voor bankieren, webshops en andere vertrouwelijke toepassingen.',
      'Het werkt doorgaans via poort 443 en wordt aangeduid met een slotje in de browser.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/HTTPS'
  },
  'DNS': {
    metaphor: 'DNS is als een telefoonboek dat namen vertaalt naar telefoonnummers zodat je iemand kunt bellen.',
    explanation: [
      'DNS staat voor Domain Name System en vertaalt gebruiksvriendelijke domeinnamen naar IP-adressen.',
      'Wanneer je een website intypt, zoekt de DNS-server het juiste IP-adres op zodat je computer verbinding kan maken.',
      'Het werkt hiërarchisch en gedistribueerd, met root-, top-level- en authoritatieve servers.',
      'DNS is essentieel omdat het onpraktisch zou zijn IP-adressen te onthouden.',
      'Het gebruikt meestal poort 53 en ondersteunt zowel UDP als TCP.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Domain_Name_System'
  },
  'ICMP': {
    metaphor: 'ICMP is als een postkantoor dat een "retour afzender"-bericht stuurt als een brief niet kan worden bezorgd.',
    explanation: [
      'ICMP staat voor Internet Control Message Protocol en wordt gebruikt voor netwerkdiagnostiek en foutmeldingen.',
      'Het is vooral bekend van het "ping"-commando om te controleren of een host bereikbaar is.',
      'Routers en apparaten gebruiken ICMP om problemen te melden zoals onbereikbare bestemmingen of verlopen tijd.',
      'Het vervoert geen gebruikersdata maar helpt het netwerkverkeer te beheren.',
      'ICMP werkt direct boven IP en gebruikt geen poorten.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Internet_Control_Message_Protocol'
  },
  'TCP': {
    metaphor: 'TCP is als een telefoongesprek waarbij beide partijen eerst bevestigen dat ze elkaar horen.',
    explanation: [
      'TCP staat voor Transmission Control Protocol en zorgt voor betrouwbare, geordende en foutvrije gegevensoverdracht.',
      'Het bouwt eerst een verbinding op via een three-way handshake.',
      'TCP controleert of alle pakketten aankomen en in de juiste volgorde staan, en verzendt verloren pakketten opnieuw.',
      'Het wordt gebruikt door veel toepassingen, zoals web, e-mail en bestandsoverdracht.',
      'TCP gebruikt diverse poorten, zoals 80 voor HTTP en 443 voor HTTPS.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Transmission_Control_Protocol'
  },
  'UDP': {
    metaphor: 'UDP is als ansichtkaarten sturen: ze komen snel aan, maar soms in de verkeerde volgorde of helemaal niet.',
    explanation: [
      'UDP staat voor User Datagram Protocol en is een lichtgewicht, verbindingsloos protocol.',
      'Het verzendt gegevens zonder verbinding of garantie voor ontvangst, volgorde of foutcontrole.',
      'UDP is sneller dan TCP maar minder betrouwbaar, ideaal voor real-time toepassingen zoals video en gaming.',
      'Het wordt gebruikt voor DNS, VoIP en vele streamingprotocollen.',
      'UDP gebruikt poorten zoals 53 voor DNS en 123 voor NTP.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/User_Datagram_Protocol'
  },
  'TLSv1.3': {
    metaphor: 'TLS is als je brief in een gesloten envelop stoppen zodat alleen de ontvanger hem kan lezen.',
    explanation: [
      'TLS staat voor Transport Layer Security en versleutelt gegevens over netwerken.',
      'TLSv1.3 is de nieuwste versie met verbeterde veiligheid en prestaties.',
      'Het wordt gebruikt om HTTPS, e-mail en andere protocollen te beveiligen.',
      'TLS waarborgt vertrouwelijkheid, integriteit en authenticiteit van gegevens onderweg.',
      'Het werkt boven TCP, meestal op poort 443 voor HTTPS.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Transport_Layer_Security#TLS_1.3'
  },
  'SMTP': {
    metaphor: 'SMTP is als een postbode die je brief ophaalt en in de brievenbus van de ontvanger stopt.',
    explanation: [
      'SMTP staat voor Simple Mail Transfer Protocol en verzendt e-mails tussen servers.',
      'Het verzorgt de aflevering van uitgaande e-mails van de verzender naar de server van de ontvanger.',
      'SMTP is een push-protocol: het duwt e-mails naar de volgende server.',
      'Voor ophalen van e-mail worden POP3 of IMAP gebruikt.',
      'SMTP gebruikt doorgaans poort 25, 465 of 587.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Simple_Mail_Transfer_Protocol'
  },
  'POP3': {
    metaphor: 'POP3 is als je post ophalen en alles mee naar huis nemen.',
    explanation: [
      'POP3 staat voor Post Office Protocol versie 3 en haalt e-mail op van een server.',
      'Bij het ophalen downloadt POP3 de berichten en verwijdert ze meestal van de server.',
      'Het is eenvoudig en geschikt voor mensen die e-mail op één apparaat lezen.',
      'POP3 ondersteunt geen servermappen of synchronisatie.',
      'Het gebruikt meestal poort 110.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Post_Office_Protocol'
  },
  'IMAP': {
    metaphor: 'IMAP is als je post lezen op het postkantoor, waarbij je alles daar laat staan.',
    explanation: [
      'IMAP staat voor Internet Message Access Protocol en geeft toegang tot e-mails op een server.',
      'In tegenstelling tot POP3 kun je berichten lezen en organiseren zonder ze te downloaden.',
      'IMAP ondersteunt meerdere apparaten en houdt alles overal gesynchroniseerd.',
      'Het is ideaal voor gebruikers die vanaf verschillende locaties of apparaten werken.',
      'IMAP gebruikt meestal poort 143 of 993 (beveiligd).'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Internet_Message_Access_Protocol'
  },
  'FTP': {
    metaphor: 'FTP is als een koeriersdienst die dozen tussen magazijnen vervoert.',
    explanation: [
      'FTP staat voor File Transfer Protocol en verplaatst bestanden tussen computers via een netwerk.',
      'Gebruikers kunnen bestanden uploaden, downloaden en beheren op externe servers.',
      'FTP is standaard niet versleuteld, dus gevoelige data moet via SFTP of FTPS.',
      'Het werkt in actieve of passieve modus voor firewall/NAT-compatibiliteit.',
      'FTP gebruikt doorgaans poorten 21 (besturing) en 20 (data).'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/File_Transfer_Protocol'
  },
  'QUIC': {
    metaphor: 'QUIC is als een hogesnelheidstrein die je sneller en veiliger op je bestemming brengt.',
    explanation: [
      'QUIC staat voor Quick UDP Internet Connections en is een modern transportprotocol ontwikkeld door Google.',
      'Het werkt over UDP en biedt functies vergelijkbaar met TCP+TLS, maar met lagere latency en snellere connecties.',
      'QUIC is bedoeld voor webverkeer, vooral HTTP/3, en presteert beter op onbetrouwbare netwerken.',
      'Het biedt ingebouwde encryptie en multiplexing van streams.',
      'QUIC wordt gebruikt door grote websites zoals Google en YouTube en gebruikt UDP, meestal poort 443.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/QUIC'
  },
  'ARP': {
    metaphor: 'ARP is als in een kamer vragen: "Wie heeft dit telefoonnummer?" tot iemand antwoordt.',
    explanation: [
      'ARP staat voor Address Resolution Protocol en vertaalt IP-adressen naar MAC-adressen binnen een lokaal netwerk.',
      'Wanneer een apparaat een ander op hetzelfde LAN wil bereiken, vraagt het via ARP om het hardware-adres.',
      'ARP-verzoeken worden als broadcast verzonden en het apparaat met het juiste IP reageert.',
      'ARP is essentieel voor lokale netwerken en werkt op laag 2.',
      'ARP gebruikt geen poorten en is niet routerbaar buiten het lokale netwerk.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Address_Resolution_Protocol'
  },
  'DHCP': {
    metaphor: 'DHCP is als een hotelreceptie die je een kamernummer toewijst bij het inchecken.',
    explanation: [
      'DHCP staat voor Dynamic Host Configuration Protocol en wijst automatisch IP-adressen toe aan apparaten op een netwerk.',
      'Bij verbinding vraagt een apparaat een IP aan en de DHCP-server geeft een vrij adres.',
      'DHCP verstrekt ook andere instellingen zoals gateway en DNS.',
      'Het vereenvoudigt netwerkbeheer door handmatige configuratie te vermijden.',
      'DHCP gebruikt UDP-poorten 67 en 68.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Dynamic_Host_Configuration_Protocol'
  },
  'mDNS': {
    metaphor: 'mDNS is als je in de buurt vraagt of iemand weet waar je vriend woont, zonder centraal register.',
    explanation: [
      'mDNS staat voor Multicast DNS en laat apparaten hostnamen omzetten naar IP-adressen binnen lokale netwerken zonder centrale server.',
      'Het wordt vaak gebruikt voor apparaatdetectie thuis of op kantoor (bijv. printers, smart-tv’s).',
      'mDNS-verzoeken gaan via multicast en apparaten reageren als zij de naam herkennen.',
      'Het werkt via UDP-poort 5353.',
      'mDNS is onderdeel van Apple’s Bonjour en vergelijkbare zero-configuration netwerken.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Multicast_DNS'
  },
  'SSDP': {
    metaphor: 'SSDP is als roepen in een kamer om te zien wie antwoordt, zodat apparaten elkaar automatisch vinden.',
    explanation: [
      'SSDP staat voor Simple Service Discovery Protocol en helpt apparaten en diensten op een lokaal netwerk elkaar te ontdekken.',
      'Het is onderdeel van het UPnP-protocol.',
      'Apparaten kondigen zich aan en luisteren naar discovery-verzoeken via multicast.',
      'SSDP gebruikt UDP-poort 1900.',
      'Het wordt vaak gebruikt door smart-tv’s, mediaservers en IoT-apparaten.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Simple_Service_Discovery_Protocol'
  },
  'SFTP': {
    metaphor: 'SFTP is als een beveiligde kluiswagen die je waardevolle spullen vervoert.',
    explanation: [
      'SFTP staat voor SSH File Transfer Protocol en is een beveiligd alternatief voor FTP.',
      'Het werkt over SSH en versleutelt alle gegevens tijdens overdracht.',
      'SFTP biedt veilige upload, download en bestandsbeheer op externe servers.',
      'Het wordt veel gebruikt voor gevoelige gegevens zoals back-ups en financiële bestanden.',
      'SFTP gebruikt standaard poort 22 (SSH).'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/SSH_File_Transfer_Protocol'
  },
  'SSH': {
    metaphor: 'SSH is als een beveiligde lijn waarmee je op afstand inlogt op een computer zonder dat iemand meeluistert.',
    explanation: [
      'SSH staat voor Secure Shell en biedt veilige toegang tot computers op afstand.',
      'Het versleutelt alle communicatie, inclusief wachtwoorden en commando’s.',
      'SSH wordt vaak gebruikt voor serverbeheer, tunnels en bestandsoverdracht (SFTP).',
      'Het ondersteunt authenticatie via wachtwoord of sleutels.',
      'SSH gebruikt standaard poort 22.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Secure_Shell'
  },
  'SNMP': {
    metaphor: 'SNMP is als een conciërge die regelmatig alle apparaten controleert en rapporteert hoe het gaat.',
    explanation: [
      'SNMP staat voor Simple Network Management Protocol en wordt gebruikt voor netwerkbeheer.',
      'Het verzamelt informatie over apparaten zoals routers, switches en printers.',
      'Beheersoftware gebruikt SNMP om status en statistieken op te vragen of waarschuwingen te ontvangen.',
      'SNMP kent verschillende versies, waarbij v3 beveiliging en encryptie biedt.',
      'SNMP gebruikt standaard UDP-poort 161 (queries) en 162 (traps).'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Simple_Network_Management_Protocol'
  },
  'NTP': {
    metaphor: 'NTP is als een atoomklok die je horloge altijd op de juiste tijd zet.',
    explanation: [
      'NTP staat voor Network Time Protocol en synchroniseert de klok van apparaten via het netwerk.',
      'Het zorgt ervoor dat computers wereldwijd nauwkeurig dezelfde tijd hanteren.',
      'NTP gebruikt hiërarchische servers (stratum-niveaus) om de tijd te verspreiden.',
      'NTP kan correcties in milliseconden uitvoeren voor nauwkeurigheid.',
      'Het gebruikt UDP-poort 123.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Network_Time_Protocol'
  },
  'LDAP': {
    metaphor: 'LDAP is als een telefoonboek waar je snel kunt opzoeken wie iemand is en welke rechten hij heeft.',
    explanation: [
      'LDAP staat voor Lightweight Directory Access Protocol en wordt gebruikt voor toegang tot directorydiensten.',
      'Het beheert gegevens zoals gebruikersaccounts, groepen en apparaten binnen organisaties.',
      'LDAP-servers worden vaak gebruikt voor authenticatie en autorisatie (Active Directory).',
      'LDAP ondersteunt hiërarchische datamodellen voor efficiënt zoeken.',
      'LDAP gebruikt standaard poort 389, LDAPS (secure) poort 636.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Lightweight_Directory_Access_Protocol'
  },
  'RDP': {
    metaphor: 'RDP is als een afstandsbediening waarmee je een andere computer volledig kunt bedienen alsof je ervoor zit.',
    explanation: [
      'RDP staat voor Remote Desktop Protocol en laat je op afstand een grafische sessie starten op een andere computer.',
      'Het wordt veel gebruikt voor support en beheer van Windows-systemen.',
      'RDP verzendt scherm- en invoergegevens tussen client en server.',
      'Het ondersteunt versleuteling en diverse beveiligingsopties.',
      'RDP gebruikt standaard TCP-poort 3389.'
    ],
    wiki: 'https://nl.wikipedia.org/wiki/Remote_Desktop_Protocol'
  }
};


// Modal logic (add this to your main HTML page)
// <div id="protocol-modal-backdrop" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);z-index:9998;"></div>
// <div id="protocol-modal" style="display:none;position:fixed;top:20%;left:50%;transform:translate(-50%,0);background:#222;color:#fff;padding:20px;border-radius:8px;z-index:9999;min-width:300px;">
//   <div id="protocol-modal-content"></div>
// </div>

document.addEventListener('click', function(e) {
  if (e.target.classList.contains('protocol-link')) {
    const proto = e.target.getAttribute('data-proto');
    const data = window.protocolExplanations[proto];
    let html = '';
    if (data) {
      html = `<div style='font-weight:bold;font-size:1.1em;margin-bottom:8px;'>${proto}</div>` +
        `<div style='font-style:italic;margin-bottom:8px;'>${data.metaphor}</div>` +
        data.explanation.map(s => `<div style='margin-bottom:4px;'>${s}</div>`).join('');
      if (data.wiki) {
        html += `<div style='margin-top:10px;'><a href='${data.wiki}' target='_blank' rel='noopener' style='color:#4ea1f7;text-decoration:underline;font-weight:500;'>Wikipedia: ${proto}</a></div>`;
      }
    } else {
      html = `<div style='font-weight:bold;'>${proto}</div><div>Geen uitleg beschikbaar.</div>`;
    }
    // Show modal and backdrop (support both: with or without backdrop)
    var modal = document.getElementById('protocol-modal');
    var backdrop = document.getElementById('protocol-modal-backdrop');
    document.getElementById('protocol-modal-content').innerHTML = html;
    if (modal) {
      modal.style.display = 'block';
    }
    if (backdrop) {
      backdrop.style.display = 'block';
    }
  }
});

// Hide modal when clicking outside or pressing Escape
document.addEventListener('DOMContentLoaded', function() {
  var modal = document.getElementById('protocol-modal');
  var backdrop = document.getElementById('protocol-modal-backdrop');
  if (backdrop && modal) {
    backdrop.addEventListener('click', function() {
      modal.style.display = 'none';
      backdrop.style.display = 'none';
    });
    document.addEventListener('keydown', function(e) {
      if (modal.style.display === 'block' && (e.key === 'Escape' || e.key === 'Esc')) {
        modal.style.display = 'none';
        backdrop.style.display = 'none';
      }
    });
  }
});
