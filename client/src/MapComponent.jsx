import React, { useEffect, useRef } from 'react';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
import L from 'leaflet';
import RoutingMachine from './RoutingMachine';

// Fix for default Leaflet marker icons not showing in React Vite setups
import icon from 'leaflet/dist/images/marker-icon.png';
import iconShadow from 'leaflet/dist/images/marker-shadow.png';

let DefaultIcon = L.icon({
  iconUrl: icon,
  shadowUrl: iconShadow,
  iconAnchor: [12, 41]
});

L.Marker.prototype.options.icon = DefaultIcon;

const createBusIcon = () => {
  return L.divIcon({
    html: `
      <div class="bus-icon-container"
           style="transition: transform 0.1s; transform-origin: center center;">
        <svg
          style="
            color:#2563eb;
            background:#ffffff;
            border-radius:50%;
            padding:2px;
            border:2px solid #2563eb;
            width:32px;
            height:32px;
          "
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2">
          <rect width="16" height="20" x="4" y="2" rx="2" ry="2"></rect>
          <path d="M9 2v2"></path>
          <path d="M15 2v2"></path>
          <path d="M4 11h16"></path>
          <path d="M8 15h.01"></path>
          <path d="M16 15h.01"></path>
          <path d="M6 22v-2"></path>
          <path d="M18 22v-2"></path>
        </svg>
      </div>
    `,
    iconSize: [32, 32],
    iconAnchor: [16, 16],
    className: ''
  });
};

const userIcon = L.divIcon({
  html: `
    <svg
      style="
        color:#10b981;
        background:#ffffff;
        border-radius:50%;
        padding:2px;
        border:2px solid #10b981;
        width:28px;
        height:28px;
      "
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2">
      <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
      <circle cx="12" cy="7" r="4"></circle>
    </svg>
  `,
  iconSize: [28, 28],
  iconAnchor: [14, 14],
  className: ''
});

// Automatically fit map bounds
function MapController({ busLocation, userLocation }) {
  const map = useMap();
  const firstLoadRef = useRef(true);

  useEffect(() => {
    if (busLocation && firstLoadRef.current) {
      if (userLocation) {
        const bounds = L.latLngBounds([busLocation, userLocation]);

        map.fitBounds(bounds, {
          padding: [50, 50],
          animate: true
        });
      } else {
        map.setView(busLocation, 14, {
          animate: true
        });
      }

      firstLoadRef.current = false;
    }
  }, [busLocation, userLocation, map]);

  return null;
}

// Smooth animated bus marker
function AnimatedBusMarker({ position, busId, eta, aiWeather }) {
  const markerRef = useRef(null);
  const prevPosition = useRef(null);
  const animationFrameId = useRef(null);
  const bearingRef = useRef(0);
  const busIconRef = useRef(createBusIcon());

  useEffect(() => {
    if (!markerRef.current || !position) return;

    if (!prevPosition.current) {
      prevPosition.current = position;
      return;
    }

    if (
      position[0] === prevPosition.current[0] &&
      position[1] === prevPosition.current[1]
    ) {
      return;
    }

    // Calculate bearing for bus rotation
    const startLat = prevPosition.current[0] * Math.PI / 180;
    const startLng = prevPosition.current[1] * Math.PI / 180;
    const destLat = position[0] * Math.PI / 180;
    const destLng = position[1] * Math.PI / 180;

    const y = Math.sin(destLng - startLng) * Math.cos(destLat);

    const x =
      Math.cos(startLat) * Math.sin(destLat) -
      Math.sin(startLat) * Math.cos(destLat) * Math.cos(destLng - startLng);

    const bearing =
      (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;

    const dist = Math.sqrt(
      Math.pow(position[0] - prevPosition.current[0], 2) +
      Math.pow(position[1] - prevPosition.current[1], 2)
    );

    if (dist > 0.00001) {
      bearingRef.current = bearing;
    }

    const startPos = [...prevPosition.current];
    const endPos = [...position];

    const startTime = performance.now();
    const duration = 3000;

    const animateMarker = (currentTime) => {
      const elapsedTime = currentTime - startTime;

      let progress = elapsedTime / duration;

      if (progress > 1) progress = 1;

      const currentLat =
        startPos[0] + (endPos[0] - startPos[0]) * progress;

      const currentLng =
        startPos[1] + (endPos[1] - startPos[1]) * progress;

      if (markerRef.current) {
        markerRef.current.setLatLng([currentLat, currentLng]);

        const el = markerRef.current.getElement();

        if (el) {
          const container = el.querySelector('.bus-icon-container');

          if (container) {
            container.style.transform = `rotate(${bearingRef.current}deg)`;
          }
        }
      }

      if (progress < 1) {
        animationFrameId.current = requestAnimationFrame(animateMarker);
      } else {
        prevPosition.current = endPos;
      }
    };

    if (animationFrameId.current) {
      cancelAnimationFrame(animationFrameId.current);
    }

    animationFrameId.current = requestAnimationFrame(animateMarker);

    return () => {
      if (animationFrameId.current) {
        cancelAnimationFrame(animationFrameId.current);
      }
    };
  }, [position]);

  return (
    <Marker
      position={prevPosition.current || position}
      icon={busIconRef.current}
      ref={markerRef}
    >
      <Popup>
        <div style={{ textAlign: 'center', minWidth: '100px' }}>
          <strong
            style={{
              display: 'block',
              marginBottom: '5px',
              fontSize: '14px',
              color: '#0f172a'
            }}
          >
            Bus {busId || '---'}
          </strong>

          {eta !== null && (
            <span
              style={{
                display: 'block',
                color: '#2563eb',
                fontWeight: 'bold'
              }}
            >
              ETA: {eta} min
            </span>
          )}

          {aiWeather && aiWeather.traffic && (
            <span
              style={{
                display: 'block',
                color: aiWeather.traffic_color,
                fontSize: '12px',
                marginTop: '4px',
                fontWeight: '600'
              }}
            >
              Traffic: {aiWeather.traffic}
            </span>
          )}
        </div>
      </Popup>
    </Marker>
  );
}

function MapComponent({
  busLocation,
  userLocation,
  busId,
  eta,
  aiWeather
}) {
  return (
    <div
      id="map-container"
      style={{
        width: '100%',
        height: '100%',
        position: 'absolute',
        top: 0,
        left: 0,
        zIndex: 0
      }}
    >
      <MapContainer
        center={[12.9141, 74.8560]}
        zoom={10}
        style={{ height: '100%', width: '100%' }}
      >
        <TileLayer
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
          attribution='&copy; OpenStreetMap contributors'
        />

        <MapController
          busLocation={busLocation}
          userLocation={userLocation}
        />

        {/* Passenger Marker */}
        {userLocation && (
          <Marker position={userLocation} icon={userIcon}>
            <Popup>You are here</Popup>
          </Marker>
        )}

        {/* Animated Bus Marker */}
        {busLocation && (
          <AnimatedBusMarker
            position={busLocation}
            busId={busId}
            eta={eta}
            aiWeather={aiWeather}
          />
        )}

        {/* TRUE ROAD ROUTING USING OSRM */}
        {userLocation && busLocation && (
          <RoutingMachine
            busLocation={busLocation}
            userLocation={userLocation}
          />
        )}
      </MapContainer>
    </div>
  );
}

export default MapComponent;
