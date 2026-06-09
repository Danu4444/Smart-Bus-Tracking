import L from "leaflet";
import { useEffect, useRef } from "react";
import { useMap } from "react-leaflet";
import "leaflet-routing-machine";

const buildWaypoints = (busLocation, userLocation) => [
  L.latLng(busLocation[0], busLocation[1]),
  L.latLng(userLocation[0], userLocation[1])
];

const RoutingMachine = ({ busLocation, userLocation }) => {
  const map = useMap();
  const routingControlRef = useRef(null);

  useEffect(() => {
    return () => {
      if (routingControlRef.current) {
        map.removeControl(routingControlRef.current);
        routingControlRef.current = null;
      }
    };
  }, [map]);

  useEffect(() => {
    if (!busLocation || !userLocation) {
      if (routingControlRef.current) {
        map.removeControl(routingControlRef.current);
        routingControlRef.current = null;
      }

      return;
    }

    const waypoints = buildWaypoints(busLocation, userLocation);

    if (routingControlRef.current) {
      routingControlRef.current.setWaypoints(waypoints);
      return;
    }

    const routingControl = L.Routing.control({
      waypoints,
      lineOptions: {
        styles: [
          { color: "#0f172a", weight: 9, opacity: 0.22 },
          { color: "#2563eb", weight: 5, opacity: 0.95 }
        ]
      },
      show: false,
      addWaypoints: false,
      draggableWaypoints: false,
      fitSelectedRoutes: true,
      showAlternatives: false,
      createMarker: () => null,
      routeWhileDragging: false,
      containerClassName: "routing-machine-hidden",
      itineraryClassName: "routing-machine-hidden",
      summaryTemplate: ""
    }).addTo(map);

    routingControl.hide();

    const container = routingControl.getContainer();

    if (container) {
      container.innerHTML = "";
      container.style.display = "none";
      container.style.visibility = "hidden";
      container.style.pointerEvents = "none";
      container.setAttribute("aria-hidden", "true");
    }

    routingControlRef.current = routingControl;
  }, [map, busLocation, userLocation]);

  return null;
};

export default RoutingMachine;
