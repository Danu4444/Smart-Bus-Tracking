# PROJECT REPORT: SMART BUS TRACKING SYSTEM

## 1. INTRODUCTION

### 1.1 Introduction of the System
The advent of digital tracking technologies and ubiquitous mobile connectivity has opened up new avenues for improving public transportation. The Smart Bus Tracking System is designed to eliminate the uncertainty and inefficiency traditionally associated with public transit commutes. By providing real-time geographical data, estimated times of arrival (ETA), and crowd density metrics, the system seeks to empower passengers with actionable information. Simultaneously, it provides fleet administrators and bus drivers with robust tools to manage routes, broadcast emergencies, and handle lost items dynamically.

#### 1.1.1 Project Title
Smart Bus Tracking System (SBTS)

#### 1.1.2 Category
Web-Based Application (Client-Server Architecture) utilizing Real-Time GPS Tracking.

#### 1.1.3 Overview
The Smart Bus Tracking System consists of three primary portals:
1. **Passenger Portal:** A React-based web interface allowing users to view live bus locations on a map, check ETAs, and communicate with drivers regarding lost items.
2. **Driver Portal:** A mobile-optimized web application that utilizes HTML5 Geolocation to continuously broadcast the bus's coordinates to the central server.
3. **Admin Portal:** A centralized command center for fleet managers to oversee all active buses, manage personnel, and respond to SOS emergencies.

### 1.2 Objectives of the System
*   **Real-Time Tracking:** To provide precise, live location updates of buses on an interactive map.
*   **Accurate ETA Calculation:** To compute Estimated Time of Arrival using routing APIs (OSRM) and the Haversine formula.
*   **Crowd Density Monitoring:** To allow drivers to update passenger density (Low, Medium, Full) so waiting passengers can make informed boarding decisions.
*   **Emergency Response:** To implement an SOS dispatch system for drivers to alert administrators instantly during breakdowns or medical emergencies.
*   **Lost and Found Management:** To facilitate direct communication between passengers and drivers for retrieving lost belongings.

### 1.3 Scope of the System
The system is scoped to handle public transport fleets operating within specific municipal or state boundaries (e.g., Karnataka). It encompasses driver tracking via standard mobile web browsers (eliminating the need for native Android/iOS apps), passenger viewing via a React Single Page Application (SPA), and administrative oversight via a dashboard. The scope currently covers GPS broadcasting, chat-based lost-and-found, and active trip management.

### 1.4 System Architecture
The application employs a standard 3-tier client-server architecture.

#### 1.4.1 Frontend
The frontend is bifurcated into two technologies:
*   **Passenger Interface:** Built using React.js and Vite, utilizing `react-leaflet` for map rendering.
*   **Driver & Admin Interfaces:** Built using Vanilla HTML5, CSS3, and JavaScript to ensure maximum compatibility and lightweight rendering on low-end mobile devices.

#### 1.4.2 Backend
The backend is powered by Vanilla PHP 8+. It exposes RESTful API endpoints that handle data ingress (driver locations, SOS alerts) and data egress (passenger map polling, admin analytics).

#### 1.4.3 Database
A relational MySQL database serves as the persistent storage layer. It handles high-frequency write operations (from drivers updating GPS coords) and read operations (from passengers polling locations).

#### 1.4.4 Hosting
The system is designed to be hosted on Apache web servers (e.g., XAMPP for local environments) with a public reverse proxy tunnel (e.g., localhost.run) to expose local APIs to mobile internet clients.

#### 1.4.5 Communication Flow
1. Driver's mobile browser captures GPS via `navigator.geolocation`.
2. Frontend sends asynchronous `POST` requests to PHP APIs via `fetch()`.
3. PHP scripts sanitize the data and execute PDO statements to update the MySQL database.
4. Passenger React app sends asynchronous `GET` requests via `axios` every 3 seconds to retrieve updated coordinates.
5. React state updates, triggering a re-render of the Leaflet Map marker.

### 1.5 End Users
#### 1.5.1 Passengers
General commuters who utilize the service to track buses, view ETAs, and report lost items. They require no specialized training to use the system.
#### 1.5.2 Drivers
Fleet employees responsible for operating the buses. They log into the system at the start of their shift, select their destination, and leave the app running to broadcast location.
#### 1.5.3 Administrators
Fleet managers who register new drivers, add new buses to the fleet, and monitor the entire system for operational efficiency and emergency interventions.

### 1.6 Software/Hardware Used
#### 1.6.1 Hardware Interface
*   **Server:** Any standard x86/x64 PC or cloud instance with minimum 2GB RAM.
*   **Driver Device:** Any smartphone with a working GPS receiver and internet connectivity.
*   **Passenger Device:** PC, laptop, tablet, or smartphone.

#### 1.6.2 Software Interface
##### 1.6.2.1 Web Browser
Google Chrome, Mozilla Firefox, Safari, or Microsoft Edge.
##### 1.6.2.2 Development Tools
VS Code, XAMPP (Apache + MySQL), Node.js (for React/Vite), Postman (for API testing).
##### 1.6.2.3 Other Software
Leaflet.js library, OpenStreetMap Tiles, OSRM (Open Source Routing Machine) API.

---

## 2. SOFTWARE REQUIREMENT SPECIFICATION (SRS)

### 2.1 Introduction
#### 2.1.1 Purpose
This SRS details the functional and non-functional requirements for the Smart Bus Tracking System. It serves as the primary reference document for developers, testers, and stakeholders.
#### 2.1.2 Scope
This document covers the tracking, ETA calculation, user management, and communication modules of the software.
#### 2.1.3 Definitions, Acronyms and Abbreviations
*   **API:** Application Programming Interface
*   **ETA:** Estimated Time of Arrival
*   **GPS:** Global Positioning System
*   **OSRM:** Open Source Routing Machine
*   **SPA:** Single Page Application

#### 2.1.4 Overview
The rest of the SRS defines the specific functional modules required, the constraints under which the system operates, and the performance characteristics expected.

### 2.2 Overall Description
#### 2.2.1 Product Perspective
SBTS is a standalone web application. It relies on third-party mapping providers (OpenStreetMap) but maintains its own proprietary database for user and fleet management.
#### 2.2.2 Product Functions
*   GPS Polling and Broadcasting
*   Real-time Map Rendering
*   Secure Login and Role-Based Access Control (RBAC)
*   Asynchronous Chat System
#### 2.2.3 User Characteristics
Users vary from highly technical administrators to non-technical passengers and drivers. The UI must be highly intuitive, utilizing universally understood icons and minimal text inputs.
#### 2.2.4 Constraints
*   **Network Reliance:** The driver app requires a persistent internet connection to broadcast location.
*   **Hardware Limitations:** Accuracy is strictly dependent on the quality of the driver's mobile GPS chip.

### 2.3 Functional Requirements
*   **REQ-01:** The system must allow admins to create, read, update, and delete (CRUD) driver profiles.
*   **REQ-02:** Drivers must be able to log in using unique credentials and select an assigned bus.
*   **REQ-03:** The driver application must capture GPS coordinates every 3 to 10 seconds.
*   **REQ-04:** Passengers must be able to search for buses by entering a 'From' and 'To' city.
*   **REQ-05:** The passenger map must auto-center on the moving bus.
*   **REQ-06:** The system must calculate distance dynamically as the bus moves.

### 2.4 Non-Functional Requirements
*   **Performance:** API endpoints must respond in under 500ms to ensure smooth map transitions.
*   **Scalability:** The database schema must support indexing on `bus_id` and `driver_id` to handle concurrent polling.
*   **Security:** Passwords must be hashed. SQL injection must be prevented via PHP PDO Prepared Statements.
*   **Usability:** The interface must be responsive (mobile-first design).

---

## 3. SYSTEM DESIGN

### 3.1 Introduction
System design bridges the gap between requirements and implementation. It defines the logical structure of the software, breaking it down into manageable modules.

### 3.2 Functional Decomposition
#### 3.2.1 Admin Module
Handles administrative tasks: user creation, fleet registration, and system oversight.
#### 3.2.2 Driver Module
Handles active trip sessions, geolocation acquisition, crowd density reporting, and SOS dispatching.
#### 3.2.3 Passenger Module
Handles searching for routes, rendering the map, displaying ETAs, and generating lost item reports.
#### 3.2.4 Tracking Module
The core engine consisting of `updateLocation.php` and `getLocation.php` that acts as the data pipeline between drivers and passengers.
#### 3.2.5 Chat & Notification Module
Handles the storage and retrieval of direct messages between passengers and drivers (`sendMsg.php`, `getMsgs.php`).

### 3.3 Description of Programs
#### 3.3.1 Context Flow Diagram
The context flow diagram illustrates the entire SBTS as a single process interacting with external entities:
*   **External Entities:** Passengers, Drivers, Admin, OSRM Routing Server.
*   **Data Flows:** GPS coords, login credentials, search queries, chat messages.

#### 3.3.2 Data Flow Diagram
##### 3.3.2.1 DFD Level 0
Shows the high-level system boundary. Passengers request locations, the System provides them. Drivers push locations to the System.
##### 3.3.2.2 DFD Level 1
Breaks the system into sub-processes: Authentication Process, Tracking Process, and Communication Process. It shows interactions with the Data Stores (MySQL).
##### 3.3.2.3 DFD Level 2
A detailed breakdown of the Tracking Process:
1. Driver GPS Sensor -> 2. Sanitize Data -> 3. Write to `bus_location` table -> 4. Read from `bus_location` table -> 5. Calculate Distance -> 6. Send to Passenger UI.

---

## 4. DATABASE DESIGN

### 4.1 Introduction
The database is structured to minimize redundancy (Third Normal Form) while optimizing for read-heavy operations on the `bus_location` table.

### 4.2 Purpose and Scope
To securely store persistent data regarding fleets, personnel, passenger history, and real-time transient data (GPS coordinates).

### 4.3 Table Definition

#### 4.3.1 Passenger Table
*   `id` (INT, PK, Auto Increment)
*   `phone` (VARCHAR, Unique)
*   `password` (VARCHAR, Hashed)
*   `created_at` (TIMESTAMP)

#### 4.3.2 Driver Table
*   `id` (INT, PK, Auto Increment)
*   `username` (VARCHAR, Unique)
*   `password` (VARCHAR, Hashed)
*   `is_active` (TINYINT)

#### 4.3.3 Bus Table
*   `bus_id` (VARCHAR, PK)
*   `bus_name` (VARCHAR)
*   `created_at` (TIMESTAMP)

#### 4.3.4 Trip Table (active_trips)
*   `id` (INT, PK)
*   `driver_id` (INT, FK)
*   `bus_id` (VARCHAR, FK)
*   `from_city` (VARCHAR)
*   `to_city` (VARCHAR)
*   `status` (VARCHAR)

#### 4.3.5 GPS Table (bus_location)
*   `id` (INT, PK)
*   `bus_id` (VARCHAR, Unique)
*   `latitude` (FLOAT)
*   `longitude` (FLOAT)
*   `updated_at` (TIMESTAMP)

#### 4.3.6 Chat Table (chats)
*   `id` (INT, PK)
*   `trip_bus_id` (VARCHAR)
*   `sender_type` (ENUM: 'passenger', 'driver', 'admin')
*   `message` (TEXT)
*   `created_at` (TIMESTAMP)

#### 4.3.7 Admin Table
*   `id` (INT, PK)
*   `username` (VARCHAR, Unique)
*   `password` (VARCHAR, MD5 Hashed)

### 4.4 ER Diagram
#### 4.4.1 Overview
The ER Diagram maps the relationships between physical entities (Drivers, Buses) and logical entities (Trips, Chats).
#### 4.4.2 Entities and Attributes
(Detailed above in Table Definitions)
#### 4.4.3 Relationships
*   One Driver manages One Active Trip (1:1)
*   One Active Trip generates Many Chat Messages (1:N)
*   One Bus has One current Location Record (1:1)
#### 4.4.4 Cardinality
Enforced via UNIQUE constraints in MySQL (e.g., `UNIQUE KEY uniq_active_driver (driver_id)`).
#### 4.4.5 Constraints
*   Foreign Key constraints prevent orphaned records.
*   Default timestamps ensure audit trails exist for all insertions.

---

## 5. DETAILED DESIGN

### 5.1 Introduction
Detailed design translates the system architecture into explicit technical specifications, outlining algorithms, API endpoints, and internal logic flows.

### 5.2 Modular Decomposition of the System
#### 5.2.1 Admin Module
*   **Logic:** Uses `fetchDrivers.php` and `adminMapData.php`. The map utilizes Leaflet.js to iterate through an array of all active `bus_location` rows, placing custom SVG markers (Blue for running, Red for SOS).
#### 5.2.2 Driver Module
*   **Logic:** Implements `navigator.geolocation.watchPosition` with `enableHighAccuracy: true`. A fallback mock-simulator generates random drift coordinates if real GPS is blocked by mobile browsers.
#### 5.2.3 Passenger Module
*   **Logic:** React Hooks (`useState`, `useEffect`) manage state. Axios triggers a `setInterval` loop to hit the backend every 3000ms.
#### 5.2.4 Tracking Module
*   **Logic:** `updateLocation.php` uses `INSERT ... ON DUPLICATE KEY UPDATE` to ensure the `bus_location` table only ever holds one active coordinate per bus, preventing database bloat.
#### 5.2.5 Chat & Notification Module
*   **Logic:** A polling interval pulls `getMsgs.php`. A hidden modal triggers via DOM manipulation when a new lost item is reported (`getLostItems.php`).
#### 5.2.6 Prediction (ETA) Module
*   **Algorithm:** 
    1. Extracts `lat/lng` of bus.
    2. Uses API call: `http://router.project-osrm.org/route/v1/driving/`
    3. Extracts `duration` (in seconds) and `distance` (in meters) from the JSON response.
    4. Converts duration to readable formats (Hrs/Mins).

### 5.3 Data Handling and Validation
*   **Frontend Validation:** HTML5 `required` attributes and JavaScript regex validation on phone numbers.
*   **Backend Validation:** PHP `isset()` and `trim()` functions prevent null insertions. PDO bindings prevent SQL injection.

### 5.4 Error Handling and Security Measures
*   **Network Errors:** Drivers are shown clear "API Network Error" indicators if the tunnel drops.
*   **Session Security:** Uses `sessionStorage` instead of `localStorage` to ensure authentication tokens are wiped upon closing the browser tab, preventing unauthorized access.

---

## 6. USER INTERFACE

### 6.1 Login Page
*Description:* A clean, glassmorphic UI card allowing users to select their portal (Passenger, Driver, Admin).
*[Insert Screenshot of index.html]*

### 6.2 Passenger Dashboard
*Description:* Features a sidebar for route selection (From/To cities) and a dynamic tracking panel displaying Live Status, Crowd Level, and ETA.
*[Insert Screenshot of Passenger Search Interface]*

### 6.3 Bus Tracking Page (Map View)
*Description:* A full-screen Leaflet map component with custom SVG markers representing the user (green) and the bus (blue). Includes a polyline depicting the mapped route.
*[Insert Screenshot of React MapComponent]*

### 6.4 Passenger Registration
*Description:* A secure form requiring phone number and password validation.
*[Insert Screenshot of Passenger Registration Modal]*

### 6.5 Driver Dashboard
*Description:* High-contrast interface designed for outdoor visibility. Features large "Start Transmitting", "Stop", and "TRIGGER SOS" buttons.
*[Insert Screenshot of Driver Dashboard]*

### 6.6 Admin Dashboard
*Description:* A multi-panel grid displaying a global map of all buses, a list of active emergencies, driver management tables, and lost item feeds.
*[Insert Screenshot of Admin Dashboard]*

### 6.7 Chat Interface (Lost & Found)
*Description:* A modal overlay within the driver and passenger apps resembling modern messaging platforms, enabling real-time text exchange.
*[Insert Screenshot of Chat Box]*

### 6.8 Notification / Alert Page
*Description:* Pulsing red UI elements alerting admins of critical SOS breakdowns.
*[Insert Screenshot of SOS Modal]*

---

## 7. TESTING

### 7.1 Introduction
Testing ensures the Smart Bus Tracking System operates reliably under varying network conditions and user loads.

### 7.2 Testing Objectives
To identify bugs in API communication, ensure GPS accuracy, validate UI responsiveness, and verify database integrity.

### 7.3 Testing Methods
*   **Black Box Testing:** Testing UI functionality without looking at backend code.
*   **White Box Testing:** API testing via Postman to ensure PHP logic correctly queries MySQL.
*   **Regression Testing:** Ensuring the change to `sessionStorage` did not break existing routing.

### 7.4 Testing Steps
#### 7.4.1 Unit Testing
Testing individual API endpoints (e.g., verifying `loginDriver.php` rejects incorrect passwords).
#### 7.4.2 Integration Testing
Verifying that when `updateLocation.php` is called, the React app's `getLocation.php` correctly reflects the new coordinates.
#### 7.4.3 Validation
Ensuring input fields do not accept SQL characters or empty submissions.
#### 7.4.4 Output Testing
Verifying the Haversine formula output matches real-world distance estimates.
#### 7.4.5 User Acceptance Testing (UAT)
Testing the driver app on a physical mobile device using a public tunnel to confirm real-world GPS acquisition.

### 7.5 Test Cases

| Test Case ID | Feature Tested | Action | Expected Result | Pass/Fail |
| :--- | :--- | :--- | :--- | :--- |
| TC-01 | Admin Login | Enter `admin` / `admin123` | Redirects to Dashboard | Pass |
| TC-02 | GPS Acquisition | Click 'Start Trip' on Mobile | Requests Location Permission | Pass |
| TC-03 | Tunnel Pathing | Access tunnel link via mobile | Loads UI without 404 errors | Pass |
| TC-04 | Security | Close tab and reopen app | User is logged out (SessionStorage) | Pass |
| TC-05 | Distance Calc | Open Passenger app on laptop | Calculates distance accurately (accounts for IP geolocation drift) | Pass |

### 7.6 Overall Testing Result
The system successfully passed all critical path test cases. Mobile browser path encoding issues (`%20`) were identified and resolved during integration testing.

---

## 8. CONCLUSION
The Smart Bus Tracking System effectively bridges the information gap between transit operators and passengers. By leveraging modern web technologies (React, PHP, Web Geolocation API), the system achieves real-time synchronization without the overhead of native app development. The architecture is robust, utilizing optimized database queries and secure session management, resulting in a highly functional, scalable, and user-friendly transportation solution.

---

## 9. LIMITATIONS
1.  **Hardware Dependency:** The passenger app relies on IP geolocation when used on desktop computers, which can result in significant distance inaccuracies (up to 50km) compared to mobile GPS.
2.  **Network Reliance:** Drivers operating in dead-zones (tunnels, rural areas) cannot broadcast location, leading to frozen ETAs.
3.  **Tunnel Volatility:** The current deployment relies on `localhost.run` tunnels which are transient and terminate upon server shutdown.

---

## 10. SCOPE OF ENHANCEMENT
1.  **Offline Caching:** Implementing Service Workers (PWA) to cache map tiles and queue GPS coordinates during temporary network loss.
2.  **Geofencing Alerts:** Automatically notifying passengers via SMS/Push Notifications when the bus enters a 2km radius of their location.
3.  **Machine Learning ETAs:** Replacing standard routing algorithms with historical traffic data analysis to improve ETA accuracy during peak hours.
4.  **Cloud Deployment:** Migrating the database and backend to a permanent cloud provider (AWS/Railway) to eliminate the need for local tunneling.

---

## 11. ACRONYMS AND ABBREVIATIONS
*   **AJAX:** Asynchronous JavaScript and XML
*   **API:** Application Programming Interface
*   **CSS:** Cascading Style Sheets
*   **DOM:** Document Object Model
*   **ETA:** Estimated Time of Arrival
*   **JSON:** JavaScript Object Notation
*   **PDO:** PHP Data Objects
*   **RBAC:** Role-Based Access Control
*   **SPA:** Single Page Application
*   **SQL:** Structured Query Language

---

## 12. BIBLIOGRAPHY AND REFERENCES
1.  React Documentation. (n.d.). *React – A JavaScript library for building user interfaces.* Retrieved from https://reactjs.org/
2.  Leaflet. (n.d.). *an open-source JavaScript library for mobile-friendly interactive maps.* Retrieved from https://leafletjs.org/
3.  PHP Documentation Group. (n.d.). *PHP: Hypertext Preprocessor.* Retrieved from https://www.php.net/docs.php
4.  Open Source Routing Machine. (n.d.). *OSRM API Documentation.* Retrieved from http://project-osrm.org/docs/v5.24.0/api/
5.  Mozilla Developer Network (MDN). (n.d.). *Geolocation API.* Retrieved from https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API
