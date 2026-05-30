/**
 * onoff-g5-base — Google Maps 내 주변 찾기
 * API 키는 PHP에서 script 로드 시에만 사용 (본 파일에 하드코딩 금지)
 */
(function (global) {
  'use strict';

  var modules = [];
  var mapsReady = false;

  function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function parseNum(val, fallback) {
    var n = parseFloat(val);
    return isFinite(n) ? n : fallback;
  }

  function isValidCoord(lat, lng) {
    return isFinite(lat) && isFinite(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
  }

  function haversineKm(lat1, lng1, lat2, lng2) {
    var R = 6371;
    var dLat = ((lat2 - lat1) * Math.PI) / 180;
    var dLng = ((lng2 - lng1) * Math.PI) / 180;
    var a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos((lat1 * Math.PI) / 180) *
        Math.cos((lat2 * Math.PI) / 180) *
        Math.sin(dLng / 2) *
        Math.sin(dLng / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
  }

  function normalizeLocation(raw) {
    if (!raw || typeof raw !== 'object') return null;
    var lat = parseNum(raw.lat, NaN);
    var lng = parseNum(raw.lng, NaN);
    if (!isValidCoord(lat, lng)) return null;
    return {
      id: raw.id != null ? raw.id : '',
      name: raw.name || '장소',
      category: raw.category || '',
      address: raw.address || '',
      lat: lat,
      lng: lng,
      phone: raw.phone || '',
      hours: raw.hours || '',
      link: raw.link || '',
      description: raw.description || '',
      tags: Array.isArray(raw.tags) ? raw.tags : [],
      distance: null,
      distanceKm: null
    };
  }

  function createMarkerInfoContent(loc) {
    var dist =
      loc.distance != null
        ? '<p class="marker-info-distance">' + escapeHtml(loc.distance) + '</p>'
        : '';
    var cat = loc.category
      ? '<p class="marker-info-category">' + escapeHtml(loc.category) + '</p>'
      : '';
    var addr = loc.address
      ? '<p class="marker-info-address">' + escapeHtml(loc.address) + '</p>'
      : '';
    var phone = loc.phone
      ? '<p class="marker-info-phone">' + escapeHtml(loc.phone) + '</p>'
      : '';
    var hours = loc.hours
      ? '<p class="marker-info-hours">' + escapeHtml(loc.hours) + '</p>'
      : '';
    var link =
      loc.link && loc.link !== '#'
        ? '<a href="' +
          escapeHtml(loc.link) +
          '" class="marker-info-link">상세보기</a>'
        : '';
    var dir =
      'https://www.google.com/maps/dir/?api=1&destination=' +
      encodeURIComponent(loc.lat + ',' + loc.lng);
    return (
      '<div class="marker-info">' +
      '<h3 class="marker-info-title">' +
      escapeHtml(loc.name) +
      '</h3>' +
      cat +
      addr +
      phone +
      hours +
      dist +
      '<div class="marker-info-actions">' +
      link +
      '<a href="' +
      escapeHtml(dir) +
      '" class="marker-info-directions" target="_blank" rel="noopener noreferrer">길찾기</a>' +
      '</div></div>'
    );
  }

  function MapModule(root) {
    this.root = root;
    this.locatorRoot = root.closest('.store-locator') || root;
    this.mapEl = root.querySelector('.google-map');
    this.placeholder = root.querySelector('.map-placeholder');
    this.resultsEl = this.locatorRoot.querySelector('.locator-results');
    this.statusEl = this.locatorRoot.querySelector('.locator-results__status');
    this.emptyEl = this.locatorRoot.querySelector('.locator-results__empty');
    this.searchInput = this.locatorRoot.querySelector('#locatorSearch');
    this.categorySelect = this.locatorRoot.querySelector('#locatorCategory');
    this.radiusSelect = this.locatorRoot.querySelector('#locatorRadius');
    this.currentBtn = this.locatorRoot.querySelector('.locator-current-location');

    var ds = this.locatorRoot.dataset;
    this.center = {
      lat: parseNum(ds.mapLat, parseNum(root.dataset.mapLat, 10.3157)),
      lng: parseNum(ds.mapLng, parseNum(root.dataset.mapLng, 123.8854))
    };
    this.zoom = parseInt(ds.mapZoom || root.dataset.mapZoom || '13', 10) || 13;
    this.dataUrl = ds.mapDataUrl || root.dataset.mapDataUrl || '';
    this.defaultRadius = parseNum(ds.mapRadius, 5);
    this.useLocation = (ds.mapUseLocation || '1') === '1';

    this.map = null;
    this.markers = [];
    this.infoWindow = null;
    this.locations = [];
    this.filtered = [];
    this.userPosition = null;
  }

  MapModule.prototype.loadMapLocations = function () {
    var self = this;
    var inline = self.root.dataset.mapLocations;
    if (inline) {
      try {
        var parsed = JSON.parse(inline);
        if (Array.isArray(parsed)) {
          return Promise.resolve(parsed.map(normalizeLocation).filter(Boolean));
        }
      } catch (e) {
        /* ignore */
      }
    }
    if (!self.dataUrl) {
      return Promise.resolve([]);
    }
    return fetch(self.dataUrl, { credentials: 'same-origin' })
      .then(function (res) {
        if (!res.ok) throw new Error('fetch failed');
        return res.json();
      })
      .then(function (data) {
        if (!Array.isArray(data)) return [];
        return data.map(normalizeLocation).filter(Boolean);
      })
      .catch(function () {
        return [];
      });
  };

  MapModule.prototype.calculateDistance = function (lat, lng) {
    return haversineKm(this.center.lat, this.center.lng, lat, lng);
  };

  MapModule.prototype.applyDistances = function () {
    var self = this;
    self.filtered.forEach(function (loc) {
      loc.distanceKm = self.calculateDistance(loc.lat, loc.lng);
      loc.distance =
        loc.distanceKm < 1
          ? (loc.distanceKm * 1000).toFixed(0) + ' m'
          : loc.distanceKm.toFixed(1) + ' km';
    });
    self.filtered.sort(function (a, b) {
      return (a.distanceKm || 0) - (b.distanceKm || 0);
    });
  };

  MapModule.prototype.filterLocations = function () {
    var self = this;
    var keyword = self.searchInput ? self.searchInput.value.trim().toLowerCase() : '';
    var category = self.categorySelect ? self.categorySelect.value : '';
    var radius = self.radiusSelect
      ? parseNum(self.radiusSelect.value, self.defaultRadius)
      : self.defaultRadius;

    self.filtered = self.locations.filter(function (loc) {
      if (category && loc.category !== category) return false;
      if (keyword) {
        var hay =
          (loc.name + ' ' + loc.address + ' ' + loc.category + ' ' + loc.tags.join(' ')).toLowerCase();
        if (hay.indexOf(keyword) === -1) return false;
      }
      return true;
    });

    self.applyDistances();

    if (radius > 0) {
      self.filtered = self.filtered.filter(function (loc) {
        return loc.distanceKm <= radius;
      });
    }
  };

  MapModule.prototype.initMap = function () {
    if (!this.mapEl || typeof global.google === 'undefined' || !global.google.maps) {
      return;
    }
    this.map = new global.google.maps.Map(this.mapEl, {
      center: this.center,
      zoom: this.zoom,
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: true
    });
    this.infoWindow = new global.google.maps.InfoWindow();
  };

  MapModule.prototype.clearMarkers = function () {
    this.markers.forEach(function (m) {
      m.setMap(null);
    });
    this.markers = [];
  };

  MapModule.prototype.renderMarkers = function () {
    var self = this;
    if (!self.map) return;
    self.clearMarkers();
    self.filtered.forEach(function (loc) {
      var marker = new global.google.maps.Marker({
        position: { lat: loc.lat, lng: loc.lng },
        map: self.map,
        title: loc.name
      });
      marker.addListener('click', function () {
        self.infoWindow.setContent(createMarkerInfoContent(loc));
        self.infoWindow.open(self.map, marker);
      });
      self.markers.push(marker);
    });
    if (self.filtered.length && self.map) {
      var bounds = new global.google.maps.LatLngBounds();
      self.filtered.forEach(function (loc) {
        bounds.extend({ lat: loc.lat, lng: loc.lng });
      });
      if (self.filtered.length === 1) {
        self.map.setCenter(bounds.getCenter());
        self.map.setZoom(self.zoom);
      } else {
        self.map.fitBounds(bounds);
      }
    }
  };

  MapModule.prototype.renderLocationList = function () {
    var self = this;
    if (!self.resultsEl) return;

    self.resultsEl.innerHTML = '';
    if (self.statusEl) {
      self.statusEl.textContent =
        self.filtered.length > 0
          ? self.filtered.length + '개 장소'
          : '조건에 맞는 장소가 없습니다.';
    }
    if (self.emptyEl) {
      self.emptyEl.hidden = self.filtered.length > 0;
    }

    self.filtered.forEach(function (loc, idx) {
      var li = document.createElement('li');
      li.className = 'locator-result-item';
      li.setAttribute('role', 'listitem');
      li.dataset.index = String(idx);
      li.innerHTML =
        '<button type="button" class="locator-result-item__btn">' +
        '<span class="locator-result-title">' +
        escapeHtml(loc.name) +
        '</span>' +
        '<span class="locator-result-meta">' +
        escapeHtml(loc.category) +
        (loc.address ? ' · ' + escapeHtml(loc.address) : '') +
        '</span>' +
        '<span class="locator-distance">' +
        escapeHtml(loc.distance || '') +
        '</span>' +
        '</button>';
      li.querySelector('button').addEventListener('click', function () {
        self.focusLocation(loc);
      });
      self.resultsEl.appendChild(li);
    });
  };

  MapModule.prototype.focusLocation = function (loc) {
    if (!this.map) return;
    this.map.panTo({ lat: loc.lat, lng: loc.lng });
    this.map.setZoom(Math.max(this.zoom, 15));
    if (this.markers.length) {
      var idx = this.filtered.indexOf(loc);
      if (idx >= 0 && this.markers[idx]) {
        global.google.maps.event.trigger(this.markers[idx], 'click');
      }
    }
  };

  MapModule.prototype.refresh = function () {
    this.filterLocations();
    this.renderMarkers();
    this.renderLocationList();
  };

  MapModule.prototype.getCurrentLocation = function () {
    var self = this;
    if (!self.useLocation || !navigator.geolocation) {
      self.setStatus('현재 위치를 사용할 수 없습니다. 기본 지역을 사용합니다.');
      self.refresh();
      return;
    }
    self.setStatus('현재 위치를 확인하는 중…');
    navigator.geolocation.getCurrentPosition(
      function (pos) {
        self.userPosition = {
          lat: pos.coords.latitude,
          lng: pos.coords.longitude
        };
        self.center = self.userPosition;
        if (self.map) {
          self.map.setCenter(self.center);
        }
        self.setStatus('현재 위치 기준으로 정렬했습니다.');
        self.refresh();
      },
      function () {
        self.setStatus('위치 권한이 거부되었습니다. 기본 지역을 사용합니다.');
        self.refresh();
      },
      { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 }
    );
  };

  MapModule.prototype.setStatus = function (msg) {
    if (this.statusEl) this.statusEl.textContent = msg || '';
  };

  MapModule.prototype.bindEvents = function () {
    var self = this;
    if (self.searchInput) {
      self.searchInput.addEventListener('input', function () {
        self.refresh();
      });
    }
    if (self.categorySelect) {
      self.categorySelect.addEventListener('change', function () {
        self.refresh();
      });
    }
    if (self.radiusSelect) {
      self.radiusSelect.addEventListener('change', function () {
        self.refresh();
      });
    }
    if (self.currentBtn) {
      self.currentBtn.addEventListener('click', function () {
        self.getCurrentLocation();
      });
    }
  };

  MapModule.prototype.start = function () {
    var self = this;
    self.bindEvents();
    self.loadMapLocations().then(function (list) {
      self.locations = list;
      if (!list.length) {
        self.setStatus('표시할 장소 데이터가 없습니다. JSON 또는 게시판 데이터를 확인하세요.');
      }
      if (self.mapEl && global.google && global.google.maps) {
        self.initMap();
      }
      self.refresh();
    });
  };

  function collectModules() {
    modules = [];
    var roots = document.querySelectorAll('.store-locator, .map-module');
    roots.forEach(function (root) {
      if (root.classList.contains('store-locator')) {
        var mapMod = root.querySelector('.map-module');
        if (mapMod && !mapMod._onoffMapBound) {
          mapMod._onoffMapBound = true;
          modules.push(new MapModule(mapMod));
        }
      } else if (!root._onoffMapBound) {
        root._onoffMapBound = true;
        modules.push(new MapModule(root));
      }
    });
  }

  function startAll() {
    collectModules();
    modules.forEach(function (m) {
      m.start();
    });
  }

  global.initOnOffGoogleMap = function () {
    mapsReady = true;
    startAll();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      if (!mapsReady) startAll();
    });
  } else if (!mapsReady) {
    startAll();
  }
})(typeof window !== 'undefined' ? window : this);
