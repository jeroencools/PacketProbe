# PacketProbe

<img width="1691" height="890" alt="Image" src="https://github.com/user-attachments/assets/d2ec211b-428f-40bc-bf15-d27ef11a7bab" />

PacketProbe is an educational web application for analyzing network packet captures (CSV format). It provides an interactive dashboard for exploring network protocols, traffic patterns, and anomalies, making it ideal for networking and cybersecurity education.

## Features

- **Interactive Dashboard:** Six customizable panels for protocol statistics, top talkers, anomaly detection, and more.
- **Protocol Exploration:** Switch between protocol views and see explanations for each protocol.
- **Anomaly Detection:** Built-in detection for port scans, rare protocols, large packets, high-frequency traffic, blacklisted IPs, and malformed packets.
- **Network Visualization:** Conversation matrix and network topology modules for visualizing communication patterns.
- **User-Friendly Interface:** Responsive design using Bootstrap 5; custom CSS for a clean look.
- **CSV Import:** Upload packet capture data in CSV format and map columns as needed.
- **Educational Focus:** Designed for classroom, lab, or self-study use.
- **Desktop Recommended:** Optimized for desktop use; mobile support is limited.

## Getting Started

1. **Clone or Download the Repository**

2. **Install Requirements**
   - Web server with PHP support (e.g., XAMPP, WAMP, MAMP, or similar).
   - Place the project folder in your web server's root directory.

3. **Run the Application**
   - Open your browser and go to:  
     `http://localhost/PacketProbe/index.php`  

## Project Structure

```
PacketProbe/
│
├── dashboard.php           # Main dashboard interface (loads all modules)
├── index.php               # Landing page, CSV upload
├── map_columns.php         # Column mapping after CSV upload
├── randomcsv.php           # Generates random/simulated packet data
│
├── modules/                # Dashboard modules (each is a PHP partial)
│   ├── AnomalyDetection.php      # Anomaly detection logic and UI
│   ├── blacklist.php             # List of blacklisted IPs
│   ├── ConversationMatrix.php    # Visualizes conversations between hosts
│   ├── NetworkTopology.php       # Network graph visualization
│   ├── PacketDetails.php         # Detailed packet table
│   ├── ProtocolPie.php           # Protocol distribution pie chart
│   └── TopTalkers.php            # Top talkers (hosts with most traffic)
│
├── assets/
│   ├── css/
│   │   └── style.css             # Custom styles
│   ├── img/
│   │   └── logo-transp-green.png # Logo
│   ├── js/
│   │   ├── dashboard.js          # Dashboard interactivity
│   │   └── protocols.js          # Protocol explanations/modal logic
│   └── protocol-modal.html       # HTML for protocol info modal
│
└── readme.md                # Project documentation
```

## Technologies Used

- **PHP** (server-side logic, dashboard, modules)
- **Bootstrap 5** (CDN, responsive UI)
- **JavaScript** (dashboard interactivity, protocol modals)
- **HTML5/CSS3** (custom styles, layout)
- **CSV** (input data format)
- **No database required** (all data is in-memory or file-based)

## Main Modules & Functions

- **AnomalyDetection.php**
  - Provides several functions for detecting network anomalies in packet data:

- **ProtocolPie.php**
  - Aggregates protocol usage statistics and displays them as a pie chart using Chart.js.

- **PacketDetails.php**
  - Displays a detailed, filterable table of all packets, with controls for filtering by time, source, destination, and protocol.

- **NetworkTopology.php**
  - Visualizes the network as a graph, showing nodes (hosts) and edges (connections) based on packet data.

- **ConversationMatrix.php**
  - Builds a matrix of communication pairs (source/destination) and counts the number of packets exchanged between each pair.

- **TopTalkers.php**
  - Identifies and visualizes the top sources or destinations by packet count or total bytes.

- **blacklist.php**
  - Contains a list of blacklisted IP addresses used by the anomaly detection module.

## Usage

- Generate or upload a packet capture CSV and map the columns.
- Explore the dashboard: switch between modules, filter data, and view protocol explanations.
- Use the app to reinforce networking concepts and analyze real or simulated traffic.

> **Warning:** PacketProbe is intended for educational and demonstration purposes only. It is not designed or recommended for use in production environments or for real-world network analysis.

## Contributing

Contributions are welcome! Fork the repository and submit a pull request.

## License

This project is for educational purposes.

## Screenshots

Home:
<img width="1691" height="890" alt="Image" src="https://github.com/user-attachments/assets/f7c6167c-9049-41b4-ae05-37885c1e6dc2" />

CSV mapping:
<img width="1691" height="890" alt="Image" src="https://github.com/user-attachments/assets/d442aff2-8e4a-42d6-b728-2182e42240e8" />

Examples:
<img width="1691" height="890" alt="Image" src="https://github.com/user-attachments/assets/f113a7ae-7255-42ac-8293-aa93a5782d8a" />

<img width="1691" height="890" alt="Image" src="https://github.com/user-attachments/assets/5e64065c-187c-448c-b3c1-6b5b8079af11" />

<img width="1691" height="890" alt="Image" src="https://github.com/user-attachments/assets/57b9182d-7e22-4b37-839d-ee6c1713dba1" />

Dark mode:
<img width="1691" height="890" alt="image" src="https://github.com/user-attachments/assets/6400c187-0107-4b94-8ca9-5180430c0b2d" />
