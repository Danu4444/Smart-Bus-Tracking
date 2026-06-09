import React, { useState, useEffect } from 'react';
import axios from 'axios';
import MapComponent from './MapComponent';
import { MapPin, Map, AlertTriangle, ShieldAlert, WifiOff, Radio, MessageSquare } from 'lucide-react';

// Auto-detect API base: local XAMPP vs Railway
const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' || window.location.hostname.includes('192.168.') || window.location.hostname.includes('lhr.life') || window.location.hostname.includes('serveo.net');
const API_BASE = isLocal
  ? `${window.location.protocol}//${window.location.host}/bus tracker/api`
  : `${window.location.protocol}//${window.location.host}/api`;

const ROOT_BASE = isLocal ? '/bus tracker' : '';

axios.defaults.withCredentials = true;

// Haversine formula to calculate distance in km
function calculateDistance(lat1, lon1, lat2, lon2) {
  const R = 6371; // Radius of the earth in km
  const dLat = (lat2 - lat1) * (Math.PI / 180);
  const dLon = (lon2 - lon1) * (Math.PI / 180);
  const a = 
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) * 
    Math.sin(dLon / 2) * Math.sin(dLon / 2); 
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)); 
  const d = R * c; // Distance in km
  return d;
}

function App() {
  const [busId, setBusId] = useState('BUS-101');
  const [locations, setLocations] = useState([]);
  const [userLocation, setUserLocation] = useState(null);
  const [distance, setDistance] = useState(null);
  const [eta, setEta] = useState(null);
  const [alertTriggered, setAlertTriggered] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [isOffline, setIsOffline] = useState(false);
  const [appMode, setAppMode] = useState('search'); // 'search' or 'tracking'
  const [searchFrom, setSearchFrom] = useState('');
  const [searchTo, setSearchTo] = useState('');
  const [searchResults, setSearchResults] = useState([]);
  const [isSearching, setIsSearching] = useState(false);
  const [alertDismissed, setAlertDismissed] = useState(false);
  const [crowdLevel, setCrowdLevel] = useState('Medium');
  
  const [showLostForm, setShowLostForm] = useState(false);
  const [lostName, setLostName] = useState('');
  const [lostPhone, setLostPhone] = useState('');
  const [lostDesc, setLostDesc] = useState('');

  const [citiesList, setCitiesList] = useState([]);
  const [userPhone, setUserPhone] = useState(null);
  const [sessionReady, setSessionReady] = useState(false);
  
  const [aiWeather, setAiWeather] = useState(null);
  const [lastPingSecs, setLastPingSecs] = useState(null);
  const [liveStatus, setLiveStatus] = useState('Idle');
  const [chatMessages, setChatMessages] = useState([]);
  const [chatInput, setChatInput] = useState('');

  const [passengerToast, setPassengerToast] = useState(null);

  // Device Detection
  const [isMobile, setIsMobile] = useState(window.innerWidth <= 768);
  useEffect(() => {
    const handleResize = () => setIsMobile(window.innerWidth <= 768);
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  useEffect(() => {
    if (!passengerToast) return undefined;
    const t = setTimeout(() => setPassengerToast(null), 3400);
    return () => clearTimeout(t);
  }, [passengerToast]);

  useEffect(() => {
    axios
      .get(`${API_BASE}/passengerSession.php`)
      .then((res) => {
        if (res.data.authenticated && res.data.phone) {
          setUserPhone(res.data.phone);
        } else {
          window.location.href = `${ROOT_BASE}/passenger/passengerLogin.html`;
        }
      })
      .catch(() => {
        window.location.href = `${ROOT_BASE}/passenger/passengerLogin.html`;
      })
      .finally(() => setSessionReady(true));
  }, []);

  // Fetch Cities for Autocomplete
  useEffect(() => {
     const fetchCities = async () => {
        try {
            const res = await axios.get(`${API_BASE}/getCities.php`);
            if(res.data.cities) {
                setCitiesList(res.data.cities);
            }
        } catch(e) {
            console.error(e);
        }
     };
     fetchCities();
  }, []);

  // User Geolocation
  useEffect(() => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          setUserLocation([position.coords.latitude, position.coords.longitude]);
        },
        (err) => {
          console.error("Error getting user location:", err);
          setError("Location permission denied. User location unavailable.");
        }
      );
    }
  }, []);

  // Poll Bus data ONLY during tracking mode
  useEffect(() => {
    if (!busId || appMode !== 'tracking') return;

    const fetchBusLocation = async () => {
      try {
        const response = await axios.get(`${API_BASE}/getLocation.php?bus_id=${busId}`);
        const data = response.data.locations; // array of {latitude, longitude, updated_at} sorted ASC
        
        try {
            const chatRes = await axios.get(`${API_BASE}/getMsgs.php?bus_id=${busId}`);
            if(chatRes.data.chats) setChatMessages(chatRes.data.chats);
        } catch(ce) { console.error("Chat poll error", ce); }

        if (response.data.crowd_level) {
            setCrowdLevel(response.data.crowd_level);
        }
        if (typeof response.data.last_ping_secs === 'number') setLastPingSecs(response.data.last_ping_secs);
        if (response.data.status) setLiveStatus(response.data.status);

        if (data && data.length > 0) {
          setError(null);
          setLocations(data);

          const latestObj = data[data.length - 1];
          const latestLat = latestObj.latitude;
          const latestLng = latestObj.longitude;

          // Check if data is stale (Offline). If older than 60 seconds, mark as offline.
          const pingAgeMs = Date.now() - new Date(latestObj.updated_at).getTime();
          const isStale = pingAgeMs > 60000;
          setIsOffline(isStale);
          
          if (userLocation) {
            const dist = calculateDistance(userLocation[0], userLocation[1], latestLat, latestLng);
            setDistance(dist.toFixed(2));
            
            // Dynamic ETA Calculation
            let speedKmh = 30; // Default fallback speed
            if (data.length >= 2) {
              const curr = data[data.length - 1];
              const prev = data[data.length - 2];
              
              const dDist = calculateDistance(prev.latitude, prev.longitude, curr.latitude, curr.longitude);
              const t1 = new Date(prev.updated_at).getTime();
              const t2 = new Date(curr.updated_at).getTime();
              const dtHours = (t2 - t1) / (1000 * 60 * 60);
              
              if (dtHours > 0 && dDist > 0) {
                const currentSpeed = dDist / dtHours;
                // Use calculated speed if it's realistic (between 1 and 120 km/h) avoids GPS glitches
                if (currentSpeed > 1 && currentSpeed <= 120) {
                    speedKmh = currentSpeed;
                }
              }
            }
            
            // Ensure minimum speed of 5km/h for calculation to avoid getting stuck at 'empty' ETAs
            speedKmh = Math.max(speedKmh, 5); 
            
            // AI Weather ETA Prediction
            try {
                const res = await axios.get(`${API_BASE}/predictEta.php?dist=${dist}&speed=${speedKmh}&lat=${latestLat}&lng=${latestLng}&userLat=${userLocation[0]}&userLng=${userLocation[1]}`);
                if (res.data) {
                    setEta(res.data.eta_mins);
                    setAiWeather(res.data);
                }
            } catch(e) { console.error("Weather AI Error:", e); }

            // Alert logic (200m proximity)
            if (dist <= 0.20 && !alertTriggered && !alertDismissed) {
              setAlertTriggered(true);
              if (navigator.vibrate) {
                navigator.vibrate([200, 100, 200]);
              }
            } else if (dist > 0.25 && alertTriggered) {
              // Reset alert if bus moves away
              setAlertTriggered(false);
              setAlertDismissed(false);
            }
          }
        } else {
          setLocations([]);
          setDistance(null);
          setEta(null);
        }
      } catch (err) {
        console.error("API Error:", err);
        setError("Failed to fetch bus data. Waiting to retry...");
      } finally {
        setLoading(false);
      }
    };

    setLoading(true);
    fetchBusLocation();
    const interval = setInterval(fetchBusLocation, 3000);
    return () => clearInterval(interval);
  }, [busId, userLocation, alertTriggered, alertDismissed, appMode]);

  const currentBusLocation = locations.length > 0 
    ? [locations[locations.length - 1].latitude, locations[locations.length - 1].longitude] 
    : null;

  const handleSearch = async (e) => {
    e.preventDefault();
    if (!searchFrom || !searchTo) {
      setSearchResults([]);
      return;
    }
    if (searchFrom === searchTo) {
      setError("FROM and TO cannot be same.");
      return;
    }
    if (!citiesList.includes(searchFrom) || !citiesList.includes(searchTo)) {
      setError("Please select cities from autocomplete only.");
      return;
    }
    setError(null);
    setIsSearching(true);
    try {
      const res = await axios.get(`${API_BASE}/searchBuses.php?from=${encodeURIComponent(searchFrom)}&to=${encodeURIComponent(searchTo)}`);
      setSearchResults(res.data.results || []);
    } catch(err) {
      console.error("Error searching buses", err);
      setSearchResults([]);
    } finally {
      setIsSearching(false);
    }
  };

  const [recentTrips, setRecentTrips] = useState([]);

  useEffect(() => {
      if (userPhone && appMode === 'search') {
          const fetchHistory = async () => {
              try {
                  const res = await axios.get(`${API_BASE}/getHistory.php?phone=${userPhone}`);
                  if(res.data.history) setRecentTrips(res.data.history);
              } catch(e) { console.error(e); }
          };
          fetchHistory();
      }
  }, [userPhone, appMode]);

  const startTracking = async (busObj) => {
    // Save to DB recent trips
    try {
        await axios.post(`${API_BASE}/saveHistory.php`, {
            phone: userPhone,
            bus_id: busObj.bus_id,
            from_city: busObj.from_city,
            to_city: busObj.to_city
        });
    } catch(e) { console.error("Error saving history", e); }

    setBusId(busObj.bus_id);
    setAppMode('tracking');
    setLocations([]);
    setDistance(null);
    setEta(null);
    setAlertTriggered(false);
    setAlertDismissed(false);
    setShowLostForm(false);
  };

  const submitLostItem = async (e) => {
      e.preventDefault();
      try {
        await axios.post(`${API_BASE}/reportLostItem.php`, {
            bus_id: busId,
            passenger_name: lostName,
            passenger_phone: lostPhone,
            item_description: lostDesc
        });
        setPassengerToast({ message: 'Report sent instantly to the driver!', type: 'success' });
        setShowLostForm(false);
        setLostName(''); setLostPhone(''); setLostDesc('');
      } catch(e) {
          setPassengerToast({ message: 'Error submitting. Please try again.', type: 'error' });
          console.error(e);
      }
  };

  const submitChat = async (e) => {
      e.preventDefault();
      if(!chatInput.trim()) return;
      try {
          await axios.post(`${API_BASE}/sendMsg.php`, {
              trip_bus_id: busId,
              sender_type: 'passenger',
              sender_id: userPhone,
              message: chatInput
          });
          setChatInput('');
      } catch(e) {
          console.error("Chat error:", e);
      }
  };

  if (!sessionReady || !userPhone) {
    return (
      <div className="app-container" style={{ justifyContent: 'center', alignItems: 'center' }}>
        <p style={{ color: '#64748b', fontWeight: 600 }}>Loading session…</p>
      </div>
    );
  }

  if (appMode === 'search') {
    return (
      <div className="app-container search-screen">
        <header className="app-top-bar">
              <h1>Smart Bus Tracker</h1>
              <div className="app-top-bar-actions">
              <button type="button" className="btn-nav-ghost btn-nav-danger" onClick={() => { window.location.href = `${ROOT_BASE}/logout.php`; }}>Logout</button>
              </div>
        </header>
        <div className="main-content">
        <div className="search-card">
          <h2><MapPin size={24} style={{verticalAlign:'middle', marginRight:'8px'}} /> Find Your Route</h2>
          <form className="search-form" onSubmit={handleSearch}>
            <datalist id="cities">
                {citiesList.map(c => <option key={c} value={c} />)}
            </datalist>

            <div className="form-group">
              <label>From City</label>
              <input type="text" className="input-field" placeholder="e.g. Mangalore" list="cities"
                 value={searchFrom} onChange={(e) => setSearchFrom(e.target.value)} />
            </div>
            <div className="form-group">
              <label>To City</label>
              <input type="text" className="input-field" placeholder="e.g. Bangalore" list="cities"
                 value={searchTo} onChange={(e) => setSearchTo(e.target.value)} />
            </div>
            <button type="submit" disabled={isSearching} className="search-btn">
              {isSearching ? 'Searching...' : 'Search Buses'}
            </button>
          </form>

          <div className="search-results">
            {searchResults.length > 0 ? (
              <ul className="results-list">
                {searchResults.map((bus) => {
                  const running = bus.status === 'Running';
                  return (
                  <li key={bus.bus_id} className={'result-item' + (running ? '' : ' result-item--idle')} onClick={() => startTracking(bus)} role="button" tabIndex={0}>
                    <div className="bus-info">
                      <strong>{bus.bus_name}</strong>
                      <span className="bus-id-badge">{bus.bus_id}</span>
                    </div>
                    <div className="route-info">
                      <span>{bus.from_city} → {bus.to_city}</span>
                      <span className={'status-pill ' + (running ? 'status-pill--running' : 'status-pill--idle')}>{bus.status || 'Idle'}</span>
                    </div>
                    <div className="route-info" style={{ marginTop: '0.35rem', fontSize: '0.8rem' }}>
                      Tap to track live · ETA updates once tracking starts
                    </div>
                  </li>
                  );
                })}
              </ul>
            ) : (
                <div className="no-results-msg">No active buses right now.</div>
            )}
          </div>

          {recentTrips.length > 0 && (
              <div className="recent-trips-section">
                  <h3>Recent Trips</h3>
                  <ul className="results-list">
                      {recentTrips.map((trip, idx) => (
                          <li key={'recent-' + trip.bus_id + '-' + idx} className="result-item" style={{display:'flex', flexDirection:'column'}}>
                              <div style={{display:'flex', justifyContent:'space-between', width:'100%', cursor:'pointer', gap:'0.5rem'}} onClick={() => startTracking(trip)}>
                                  <div className="bus-info">
                                    <strong>{trip.bus_name}</strong>
                                    <span className="bus-id-badge">{trip.bus_id}</span>
                                  </div>
                                  <div className="route-info" style={{textAlign:'right'}}>
                                    {trip.from_city} → {trip.to_city}<br/>
                                    <small style={{color:'#64748b'}}>{trip.time}</small>
                                  </div>
                              </div>
                              <button type="button" className="btn-lost-inline" onClick={(e) => { e.stopPropagation(); setBusId(trip.bus_id); setShowLostForm(true); }}>
                                  <ShieldAlert size={16} /> Report Lost Item
                              </button>
                          </li>
                      ))}
                  </ul>
              </div>
          )}

        </div>
        </div>
      </div>
    );
  }

  return (
    <div className="app-container">
      <header className="app-top-bar">
        <h1>Smart Bus Tracker</h1>
        <div className="app-top-bar-actions">
          <span style={{ fontSize: '0.8rem', color: '#64748b', fontWeight: 600 }}>Bus {busId}</span>
          <button type="button" className="btn-nav-ghost btn-nav-danger" onClick={() => { window.location.href = `${ROOT_BASE}/logout.php`; }}>Logout</button>
        </div>
      </header>

      {passengerToast && (
        <div className="react-toast-host" aria-live="polite">
          <div className={'ui-toast ui-toast--show ui-toast--' + passengerToast.type}>{passengerToast.message}</div>
        </div>
      )}

      {alertTriggered && !alertDismissed && (
        <div className="alert-popup">
          <span style={{ display: 'flex', alignItems: 'center', gap: '8px' }}><AlertTriangle size={18}/> The bus is arriving! (Within 200m)</span>
          <button type="button" className="btn-nav-ghost" style={{ background: '#fff', color: '#0f172a', border: 'none' }} onClick={() => setAlertDismissed(true)}>Got it</button>
        </div>
      )}

      {showLostForm && (
          <div className="lost-overlay">
              <div className="lost-card">
                  <h3><ShieldAlert size={20}/> Report Lost Item</h3>
                  <form onSubmit={submitLostItem}>
                      <input required type="text" className="input-field" placeholder="Your Name" value={lostName} onChange={e=>setLostName(e.target.value)} />
                      <input required type="text" className="input-field" placeholder="Mobile Number" value={lostPhone} onChange={e=>setLostPhone(e.target.value)} />
                      <textarea required className="input-field" placeholder="What did you lose? (e.g. Blue Backpack)" value={lostDesc} onChange={e=>setLostDesc(e.target.value)} />
                      <div className="lost-actions">
                          <button type="submit" className="search-btn" style={{ flex: 1, background: '#ef4444' }}>Report to Driver</button>
                          <button type="button" className="search-btn btn-outline" style={{ flex: 1, background: '#f8fafc', color: '#334155' }} onClick={()=>setShowLostForm(false)}>Cancel</button>
                      </div>
                  </form>
              </div>
          </div>
      )}

      <div className="main-content tracking-layout">
        <div className="tracking-map-wrap">
          <MapComponent 
            busLocation={currentBusLocation} 
            userLocation={userLocation} 
            route={locations}
            busId={busId}
            eta={eta}
            aiWeather={aiWeather}
          />
        </div>

        <aside className="info-panel tracking-panel">
          <p className="panel-section-title">Live trip</p>
          <div className="tracking-panel-card">
            <strong>Bus</strong> {busId}
            <div style={{ marginTop: '0.35rem', color: '#94a3b8', fontSize: '0.82rem' }}>
              Status: <strong style={{ color: '#fff' }}>{liveStatus}</strong>
              {' · '}
              Driver channel active
            </div>
          </div>

          {error && <div className="status-badge error">{error}</div>}
          {!error && loading && locations.length === 0 && <div className="status-badge">Loading...</div>}
          {!error && locations.length > 0 && (
            isOffline 
              ? <div className="status-badge error" style={{ display: 'flex', alignItems: 'center', gap: '5px' }}><WifiOff size={14}/> Offline — last update {lastPingSecs ?? '--'}s ago</div>
              : <div className="status-badge" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '0.5rem' }}>
                  <span style={{ display: 'flex', alignItems: 'center', gap: '5px', color: '#059669' }}><Radio size={14} className="pulse-icon"/> Live tracking</span>
                  <span style={{ background: crowdLevel === 'Low' ? '#22c55e' : crowdLevel === 'Full' ? '#ef4444' : '#f59e0b', color: crowdLevel === 'Medium' ? '#0f172a' : 'white', padding: '4px 10px', borderRadius: '999px', fontSize: '0.72rem', fontWeight: '700' }}>
                      {liveStatus} · {crowdLevel}
                  </span>
                </div>
          )}

          {/* Desktop-only Info: Metrics and AI */}
          {!isMobile && (
            <>
              <div className="metrics-grid">
                <div className="metric-card">
                  <div className="metric-value">{distance ? `${distance} km` : '--'}</div>
                  <div className="metric-label">Distance</div>
                </div>
                <div className="metric-card">
                  <div className="metric-value">{eta !== null ? `${eta} min` : '--'}</div>
                  <div className="metric-label">ETA</div>
                </div>
              </div>

              {aiWeather && (
                  <div className="ai-stack">
                      <div className="ai-block ai-block--weather">
                          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px' }}>
                              <strong style={{ color: '#0284c7' }}>Weather AI</strong>
                              <span>{aiWeather.icon} {aiWeather.weather}</span>
                          </div>
                      </div>

                      {aiWeather.traffic && (
                      <div className="ai-block" style={{ background: '#fffbeb', borderColor: aiWeather.traffic_color }}>
                          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px' }}>
                              <strong style={{ color: aiWeather.traffic_color, display: 'flex', alignItems: 'center', gap: '5px' }}><Map size={14}/> Traffic AI</strong>
                              <span style={{ color: '#334155' }}>{aiWeather.traffic}</span>
                          </div>
                      </div>
                      )}
                      <div className="ai-footnote">
                          AI prediction from weather &amp; traffic. {aiWeather.ai_message} (Speed impact: -{aiWeather.speed_penalty})
                      </div>
                  </div>
              )}

              <div className="chat-panel">
                  <div className="chat-panel-header"><MessageSquare size={16} color="#2563eb"/> Driver Chat</div>
                  <div className="chat-panel-body">
                      {chatMessages.length === 0 ? <span style={{ color: '#94a3b8', fontSize: '0.85rem' }}>No messages yet.</span> : null}
                      {chatMessages.map(m => (
                          <div key={m.id} style={{ textAlign: m.sender_type === 'passenger' ? 'right' : 'left' }}>
                              <div className={'chat-bubble ' + (m.sender_type === 'passenger' ? 'chat-bubble--me' : 'chat-bubble--them')}>
                                  {m.message}
                              </div>
                          </div>
                      ))}
                  </div>
                  <form className="chat-form" onSubmit={submitChat}>
                      <input type="text" value={chatInput} onChange={e=>setChatInput(e.target.value)} placeholder="Message driver..." />
                      <button type="submit">Send</button>
                  </form>
              </div>
            </>
          )}

          {/* Mobile Floating Action Buttons (Simulated via Bottom Actions) */}
          <div className={`panel-actions ${isMobile ? 'mobile-fab-container' : ''}`}>
            <button type="button" className="search-btn" style={{ border: '1px solid rgba(239,68,68,0.45)', color: '#fca5a5', background: 'rgba(239,68,68,0.15)' }} onClick={() => setShowLostForm(true)}>
              <ShieldAlert size={16}/> Report Item
            </button>
            <button type="button" className="search-btn btn-outline" onClick={() => setAppMode('search')}>
              Return to Search
            </button>
          </div>
        </aside>
      </div>
    </div>
  );
}

export default App;
