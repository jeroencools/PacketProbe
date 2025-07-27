// assets/js/protocols.js
// Protocoluitleg met metaforen, samenvattingen en NL Wikipedia links
window.protocolExplanations = {
  'HTTP': {
    metaphor: 'HTTP is like a waiter taking your order and bringing your food from the kitchen.',
    explanation: [
      'HTTP stands for HyperText Transfer Protocol and is the foundation for data transfer on the World Wide Web.',
      'It defines how messages are formatted and transmitted, and how servers and browsers respond.',
      'When you visit a website, your browser sends an HTTP request to the server, which then returns the requested page.',
      'HTTP is stateless: each request is independent and the server does not remember previous requests.',
      'It usually uses port 80 and is not encrypted, making it unsuitable for sensitive information.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol'
  },
  'HTTPS': {
    metaphor: 'HTTPS is like sending your order to the kitchen in a locked box that only the kitchen can open.',
    explanation: [
      'HTTPS stands for HyperText Transfer Protocol Secure and is the secure version of HTTP.',
      'It uses encryption (usually TLS) to protect data between browser and server.',
      'This keeps sensitive information like passwords and payment details safe from eavesdropping.',
      'HTTPS is essential for banking, online shopping, and other confidential applications.',
      'It typically uses port 443 and is indicated by a padlock in the browser.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/HTTPS'
  },
  'DNS': {
    metaphor: 'DNS is like a phone book that translates names into phone numbers so you can call someone.',
    explanation: [
      'DNS stands for Domain Name System and translates user-friendly domain names into IP addresses.',
      'When you type a website address, the DNS server looks up the correct IP address so your computer can connect.',
      'It works hierarchically and is distributed, with root, top-level, and authoritative servers.',
      'DNS is essential because remembering IP addresses would be impractical.',
      'It usually uses port 53 and supports both UDP and TCP.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Domain_Name_System'
  },
  'ICMP': {
    metaphor: 'ICMP is like a post office sending a "return to sender" message if a letter cannot be delivered.',
    explanation: [
      'ICMP stands for Internet Control Message Protocol and is used for network diagnostics and error messages.',
      'It is best known for the "ping" command to check if a host is reachable.',
      'Routers and devices use ICMP to report problems such as unreachable destinations or expired time.',
      'It does not carry user data but helps manage network traffic.',
      'ICMP works directly above IP and does not use ports.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Internet_Control_Message_Protocol'
  },
  'TCP': {
    metaphor: 'TCP is like a phone call where both parties first confirm they can hear each other.',
    explanation: [
      'TCP stands for Transmission Control Protocol and ensures reliable, ordered, and error-free data transfer.',
      'It first establishes a connection via a three-way handshake.',
      'TCP checks that all packets arrive and are in the correct order, and retransmits lost packets.',
      'It is used by many applications, such as web, email, and file transfer.',
      'TCP uses various ports, such as 80 for HTTP and 443 for HTTPS.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Transmission_Control_Protocol'
  },
  'UDP': {
    metaphor: 'UDP is like sending postcards: they arrive quickly, but sometimes out of order or not at all.',
    explanation: [
      'UDP stands for User Datagram Protocol and is a lightweight, connectionless protocol.',
      'It sends data without connection or guarantee of delivery, order, or error checking.',
      'UDP is faster than TCP but less reliable, ideal for real-time applications like video and gaming.',
      'It is used for DNS, VoIP, and many streaming protocols.',
      'UDP uses ports such as 53 for DNS and 123 for NTP.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/User_Datagram_Protocol'
  },
  'TLSv1.3': {
    metaphor: 'TLS is like putting your letter in a sealed envelope so only the recipient can read it.',
    explanation: [
      'TLS stands for Transport Layer Security and encrypts data over networks.',
      'TLSv1.3 is the latest version with improved security and performance.',
      'It is used to secure HTTPS, email, and other protocols.',
      'TLS ensures confidentiality, integrity, and authenticity of data in transit.',
      'It works above TCP, usually on port 443 for HTTPS.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Transport_Layer_Security'
  },
  'SMTP': {
    metaphor: 'SMTP is like a mailman who picks up your letter and puts it in the recipient\'s mailbox.',
    explanation: [
      'SMTP stands for Simple Mail Transfer Protocol and sends emails between servers.',
      'It handles the delivery of outgoing emails from the sender to the recipient\'s server.',
      'SMTP is a push protocol: it pushes emails to the next server.',
      'POP3 or IMAP are used for retrieving email.',
      'SMTP typically uses port 25, 465, or 587.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Simple_Mail_Transfer_Protocol'
  },
  'POP3': {
    metaphor: 'POP3 is like picking up your mail and taking everything home.',
    explanation: [
      'POP3 stands for Post Office Protocol version 3 and retrieves email from a server.',
      'When retrieving, POP3 downloads the messages and usually deletes them from the server.',
      'It is simple and suitable for people who read email on one device.',
      'POP3 does not support server folders or synchronization.',
      'It usually uses port 110.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Post_Office_Protocol'
  },
  'IMAP': {
    metaphor: 'IMAP is like reading your mail at the post office, leaving everything there.',
    explanation: [
      'IMAP stands for Internet Message Access Protocol and gives access to emails on a server.',
      'Unlike POP3, you can read and organize messages without downloading them.',
      'IMAP supports multiple devices and keeps everything synchronized everywhere.',
      'It is ideal for users who work from different locations or devices.',
      'IMAP usually uses port 143 or 993 (secure).'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Internet_Message_Access_Protocol'
  },
  'FTP': {
    metaphor: 'FTP is like a courier service transporting boxes between warehouses.',
    explanation: [
      'FTP stands for File Transfer Protocol and moves files between computers over a network.',
      'Users can upload, download, and manage files on remote servers.',
      'FTP is not encrypted by default, so sensitive data should use SFTP or FTPS.',
      'It works in active or passive mode for firewall/NAT compatibility.',
      'FTP typically uses ports 21 (control) and 20 (data).'
    ],
    wiki: 'https://en.wikipedia.org/wiki/File_Transfer_Protocol'
  },
  'QUIC': {
    metaphor: 'QUIC is like a high-speed train that gets you to your destination faster and more securely.',
    explanation: [
      'QUIC stands for Quick UDP Internet Connections and is a modern transport protocol developed by Google.',
      'It works over UDP and offers features similar to TCP+TLS, but with lower latency and faster connections.',
      'QUIC is intended for web traffic, especially HTTP/3, and performs better on unreliable networks.',
      'It provides built-in encryption and multiplexing of streams.',
      'QUIC is used by major websites like Google and YouTube and uses UDP, usually port 443.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/QUIC'
  },
  'ARP': {
    metaphor: 'ARP is like asking in a room: "Who has this phone number?" until someone answers.',
    explanation: [
      'ARP stands for Address Resolution Protocol and translates IP addresses to MAC addresses within a local network.',
      'When a device wants to reach another on the same LAN, it uses ARP to ask for the hardware address.',
      'ARP requests are sent as broadcasts and the device with the correct IP responds.',
      'ARP is essential for local networks and works at layer 2.',
      'ARP does not use ports and is not routable outside the local network.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Address_Resolution_Protocol'
  },
  'DHCP': {
    metaphor: 'DHCP is like a hotel reception assigning you a room number when you check in.',
    explanation: [
      'DHCP stands for Dynamic Host Configuration Protocol and automatically assigns IP addresses to devices on a network.',
      'When connecting, a device requests an IP and the DHCP server gives a free address.',
      'DHCP also provides other settings like gateway and DNS.',
      'It simplifies network management by avoiding manual configuration.',
      'DHCP uses UDP ports 67 and 68.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Dynamic_Host_Configuration_Protocol'
  },
  'mDNS': {
    metaphor: 'mDNS is like asking around locally if anyone knows where your friend lives, without a central register.',
    explanation: [
      'mDNS stands for Multicast DNS and lets devices resolve hostnames to IP addresses within local networks without a central server.',
      'It is often used for device discovery at home or in offices (e.g., printers, smart TVs).',
      'mDNS requests are sent via multicast and devices respond if they recognize the name.',
      'It works via UDP port 5353.',
      'mDNS is part of Apple’s Bonjour and similar zero-configuration networks.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Multicast_DNS'
  },
  'SSDP': {
    metaphor: 'SSDP is like shouting in a room to see who answers, so devices can automatically find each other.',
    explanation: [
      'SSDP stands for Simple Service Discovery Protocol and helps devices and services on a local network discover each other.',
      'It is part of the UPnP protocol.',
      'Devices announce themselves and listen for discovery requests via multicast.',
      'SSDP uses UDP port 1900.',
      'It is often used by smart TVs, media servers, and IoT devices.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Simple_Service_Discovery_Protocol'
  },
  'SFTP': {
    metaphor: 'SFTP is like an armored truck securely transporting your valuables.',
    explanation: [
      'SFTP stands for SSH File Transfer Protocol and is a secure alternative to FTP.',
      'It works over SSH and encrypts all data during transfer.',
      'SFTP provides secure upload, download, and file management on remote servers.',
      'It is widely used for sensitive data such as backups and financial files.',
      'SFTP uses port 22 (SSH) by default.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/SSH_File_Transfer_Protocol'
  },
  'SSH': {
    metaphor: 'SSH is like a secure line allowing you to log in to a computer remotely without anyone eavesdropping.',
    explanation: [
      'SSH stands for Secure Shell and provides secure remote access to computers.',
      'It encrypts all communication, including passwords and commands.',
      'SSH is often used for server management, tunnels, and file transfer (SFTP).',
      'It supports authentication via password or keys.',
      'SSH uses port 22 by default.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Secure_Shell'
  },
  'SNMP': {
    metaphor: 'SNMP is like a concierge regularly checking all devices and reporting how they are doing.',
    explanation: [
      'SNMP stands for Simple Network Management Protocol and is used for network management.',
      'It collects information about devices such as routers, switches, and printers.',
      'Management software uses SNMP to query status and statistics or receive alerts.',
      'SNMP has different versions, with v3 offering security and encryption.',
      'SNMP uses UDP port 161 (queries) and 162 (traps) by default.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Simple_Network_Management_Protocol'
  },
  'NTP': {
    metaphor: 'NTP is like an atomic clock that always sets your watch to the correct time.',
    explanation: [
      'NTP stands for Network Time Protocol and synchronizes the clock of devices over the network.',
      'It ensures that computers worldwide keep the same accurate time.',
      'NTP uses hierarchical servers (stratum levels) to distribute the time.',
      'NTP can make corrections in milliseconds for accuracy.',
      'It uses UDP port 123.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Network_Time_Protocol'
  },
  'LDAP': {
    metaphor: 'LDAP is like a phone book where you can quickly look up who someone is and what rights they have.',
    explanation: [
      'LDAP stands for Lightweight Directory Access Protocol and is used for accessing directory services.',
      'It manages data such as user accounts, groups, and devices within organizations.',
      'LDAP servers are often used for authentication and authorization (Active Directory).',
      'LDAP supports hierarchical data models for efficient searching.',
      'LDAP uses port 389 by default, LDAPS (secure) uses port 636.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Lightweight_Directory_Access_Protocol'
  },
  'RDP': {
    metaphor: 'RDP is like a remote control that lets you fully operate another computer as if you were sitting in front of it.',
    explanation: [
      'RDP stands for Remote Desktop Protocol and allows you to start a graphical session on another computer remotely.',
      'It is widely used for support and management of Windows systems.',
      'RDP transmits screen and input data between client and server.',
      'It supports encryption and various security options.',
      'RDP uses TCP port 3389 by default.'
    ],
    wiki: 'https://en.wikipedia.org/wiki/Remote_Desktop_Protocol'
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
