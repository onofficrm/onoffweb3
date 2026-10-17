import React, { createContext, useContext, useState, useEffect, useRef } from 'react';
import {
  Language,
  ScreenType,
  TabType,
  ServiceItem,
  VehicleTypeId,
  PaymentMethodId,
  DispatchRequest,
  AdminUser,
  UploadedPhoto,
} from '../types';
import { SERVICES_DATA, CEBU_MOCK_LOCATIONS } from '../data/mockData';
import {
  DEFAULT_VIEW_PIN,
  getLatestOpenDispatch,
  isPinUnlocked,
  loadDispatchHistory,
  loadSavedViewPin,
  saveDispatchHistory,
  saveViewPinLocal,
  setPinUnlocked,
  upsertDispatch,
  verifyViewPin,
  wipeDeviceLocalData,
} from '../lib/dispatchStorage';
import {
  isDispatchPhone,
  isOnsiteLookupCode,
  lookupDispatchRemote,
  normalizePhone,
  saveDispatchRemote,
  adminListDispatches,
  adminSetStatus,
  adminLogin as adminLoginRemote,
  adminLogout as adminLogoutRemote,
  adminMe,
  adminRegister as adminRegisterRemote,
  adminListAccounts as adminListAccountsRemote,
  adminDeleteAccount as adminDeleteAccountRemote,
  adminChangePassword as adminChangePasswordRemote,
  adminResetPassword as adminResetPasswordRemote,
  adminFetchPhoto,
} from '../lib/dispatchApi';
import { clearDeviceAdmin, markDeviceAsAdmin } from '../lib/adminNative';
import {
  clearAdminSession,
  loadAdminSession,
  saveAdminSession,
} from '../lib/adminAuthStorage';
import { releaseUploadedPhotos } from '../lib/photoProcessing';

interface AppContextType {
  lang: Language;
  setLang: (lang: Language) => void;
  currentScreen: ScreenType;
  navigateTo: (screen: ScreenType) => void;
  currentTab: TabType;
  setCurrentTab: (tab: TabType) => void;
  selectedService: ServiceItem | null;
  setSelectedService: (service: ServiceItem | null) => void;
  serviceCondition: string;
  setServiceCondition: (condition: string) => void;
  activeDispatch: DispatchRequest | null;
  setActiveDispatch: React.Dispatch<React.SetStateAction<DispatchRequest | null>>;
  dispatchHistory: DispatchRequest[];
  openDispatchById: (id: string) => void;
  viewPin: string;
  setViewPin: (pin: string) => void;
  pinUnlocked: boolean;
  unlockWithPin: (pin: string) => boolean;
  lockPin: () => void;
  privacyLocked: boolean;
  setPrivacyLocked: (locked: boolean) => void;
  locationAddress: string;
  setLocationAddress: (addr: string) => void;
  locationDetails: string;
  setLocationDetails: (details: string) => void;
  landmark: string;
  setLandmark: (landmark: string) => void;
  latitude: number;
  longitude: number;
  setCoordinates: (lat: number, lng: number) => void;
  selectedVehicleType: VehicleTypeId;
  setSelectedVehicleType: (type: VehicleTypeId) => void;
  vehicleModelName: string;
  setVehicleModelName: (model: string) => void;
  plateNumber: string;
  setPlateNumber: (plate: string) => void;
  contactPhone: string;
  setContactPhone: (phone: string) => void;
  issueNotes: string;
  setIssueNotes: (notes: string) => void;
  uploadedPhotos: UploadedPhoto[];
  setUploadedPhotos: React.Dispatch<React.SetStateAction<UploadedPhoto[]>>;
  isDispatchSubmitting: boolean;
  dispatchUploadProgress: number;
  dispatchSubmitError: string | null;
  clearDispatchSubmitError: () => void;
  paymentMethod: PaymentMethodId;
  setPaymentMethod: (method: PaymentMethodId) => void;
  toastMessage: string | null;
  showToast: (msg: string) => void;
  activeModal: string | null;
  openModal: (modalId: string) => void;
  closeModal: () => void;
  activeBottomSheet: string | null;
  openBottomSheet: (sheetId: string) => void;
  closeBottomSheet: () => void;
  startFastRescue: (serviceId?: string) => void;
  submitDispatchRequest: () => Promise<boolean>;
  cancelDispatchRequest: () => void;
  completeCurrentRescue: () => void;
  lookupRemoteRequests: (
    phone: string,
    pin: string
  ) => Promise<{ ok: boolean; error?: string }>;
  wipeThisDevice: () => void;
  resetForNewRequest: () => void;
  isAdminAuthenticated: boolean;
  adminUser: AdminUser | null;
  allDispatches: DispatchRequest[];
  loginAdmin: (adminId: string, password: string) => Promise<{ ok: boolean; error?: string }>;
  logoutAdmin: () => void;
  registerAdmin: (
    adminId: string,
    password: string,
    name: string
  ) => Promise<{ ok: boolean; requiresLogin?: boolean; error?: string }>;
  listAdminAccounts: () => Promise<{ ok: boolean; items?: AdminUser[]; error?: string }>;
  deleteAdminAccount: (adminId: string) => Promise<{ ok: boolean; error?: string }>;
  changeAdminPassword: (
    currentPassword: string,
    newPassword: string
  ) => Promise<{ ok: boolean; error?: string }>;
  resetAdminPassword: (
    adminId: string,
    newPassword: string
  ) => Promise<{ ok: boolean; error?: string }>;
  loadAdminPhoto: (photoId: string) => Promise<string | null>;
  refreshAdminDispatches: () => Promise<void>;
  updateDispatchAdminStatus: (
    id: string,
    status: 'pending' | 'contacted' | 'completed'
  ) => void;
}

const AppContext = createContext<AppContextType | undefined>(undefined);

export const AppProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [lang, setLang] = useState<Language>('ko');
  const [currentScreen, setCurrentScreen] = useState<ScreenType>('home');
  const [currentTab, setCurrentTab] = useState<TabType>('home');
  const [selectedService, setSelectedService] = useState<ServiceItem | null>(null);

  const defaultLoc = CEBU_MOCK_LOCATIONS[0];
  const [locationAddress, setLocationAddress] = useState<string>('세부 시티 (Cebu City)');
  const [locationDetails, setLocationDetails] = useState<string>('세부 메트로 중심 구역');
  const [landmark, setLandmark] = useState<string>('');
  const [latitude, setLatitude] = useState<number>(defaultLoc.lat);
  const [longitude, setLongitude] = useState<number>(defaultLoc.lng);
  const [serviceCondition, setServiceCondition] = useState<string>('');

  const [selectedVehicleType, setSelectedVehicleType] = useState<VehicleTypeId>('sedan');
  const [vehicleModelName, setVehicleModelName] = useState<string>('');
  const [plateNumber, setPlateNumber] = useState<string>('');
  const [contactPhone, setContactPhone] = useState<string>('');
  const [viewPin, setViewPinState] = useState<string>(() =>
    typeof window !== 'undefined' ? loadSavedViewPin() : DEFAULT_VIEW_PIN
  );
  const [issueNotes, setIssueNotes] = useState<string>('');
  const [uploadedPhotos, setUploadedPhotos] = useState<UploadedPhoto[]>([]);
  const [isDispatchSubmitting, setIsDispatchSubmitting] = useState(false);
  const [dispatchUploadProgress, setDispatchUploadProgress] = useState(0);
  const [dispatchSubmitError, setDispatchSubmitError] = useState<string | null>(null);
  const submitLockRef = useRef(false);
  const pendingDispatchRef = useRef<DispatchRequest | null>(null);
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethodId>('cash');

  const [dispatchHistory, setDispatchHistory] = useState<DispatchRequest[]>(() =>
    typeof window !== 'undefined' ? loadDispatchHistory() : []
  );
  const [activeDispatch, setActiveDispatch] = useState<DispatchRequest | null>(() =>
    typeof window !== 'undefined' ? getLatestOpenDispatch(loadDispatchHistory()) : null
  );
  const [pinUnlocked, setPinUnlockedState] = useState<boolean>(() =>
    typeof window !== 'undefined' ? isPinUnlocked() : false
  );
  const [privacyLocked, setPrivacyLocked] = useState(false);
  const initialAdminSession = loadAdminSession();
  const [isAdminAuthenticated, setIsAdminAuthenticated] = useState(!!initialAdminSession);
  const [adminUser, setAdminUser] = useState<AdminUser | null>(
    initialAdminSession?.admin || null
  );
  const [adminToken, setAdminToken] = useState(initialAdminSession?.token || '');
  const [allDispatches, setAllDispatches] = useState<DispatchRequest[]>([]);

  const [toastMessage, setToastMessage] = useState<string | null>(null);
  const [activeModal, setActiveModal] = useState<string | null>(null);
  const [activeBottomSheet, setActiveBottomSheet] = useState<string | null>(null);

  useEffect(() => {
    const session = loadAdminSession();
    if (!session) {
      clearDeviceAdmin();
      return;
    }
    void adminMe(session.token).then((res) => {
      if (!res.ok || !res.admin) {
        clearAdminSession();
        clearDeviceAdmin();
        setAdminToken('');
        setAdminUser(null);
        setIsAdminAuthenticated(false);
        return;
      }
      saveAdminSession({ token: session.token, admin: res.admin });
      setAdminToken(session.token);
      setAdminUser(res.admin);
      setIsAdminAuthenticated(true);
      markDeviceAsAdmin();
      void refreshAdminDispatches(session.token);
    });
    // Restore the persistent admin session once on app startup.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (typeof window === 'undefined' || !('geolocation' in navigator)) return;
    const applyFallback = () => {
      setLatitude(10.3157);
      setLongitude(123.8854);
      setLocationAddress('세부 시티 중심 (Cebu City Center)');
      setLocationDetails('Fuente Osmena / Cebu Downtown');
    };
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        setLatitude(lat);
        setLongitude(lng);
        setLocationAddress('GPS 확인 위치 (Cebu Metro)');
        setLocationDetails(`위도: ${lat.toFixed(4)}, 경도: ${lng.toFixed(4)}`);
        void import('../lib/osmMaps').then(async ({ reverseGeocode, formatDriverLandmark }) => {
          try {
            const geo = await reverseGeocode(lat, lng);
            setLocationAddress(geo.address);
            setLandmark(formatDriverLandmark(geo));
            setLocationDetails(`${geo.street}, ${geo.city}`);
          } catch {
            /* keep GPS */
          }
        });
      },
      applyFallback,
      { enableHighAccuracy: false, timeout: 5000, maximumAge: 60000 }
    );
  }, []);

  useEffect(() => {
    const saved = getLatestOpenDispatch(loadDispatchHistory());
    if (!saved) return;
    if (saved.locationAddress) setLocationAddress(saved.locationAddress);
    if (saved.locationDetails) setLocationDetails(saved.locationDetails);
    if (saved.landmark) setLandmark(saved.landmark);
    if (typeof saved.latitude === 'number') setLatitude(saved.latitude);
    if (typeof saved.longitude === 'number') setLongitude(saved.longitude);
    if (saved.vehicleType) setSelectedVehicleType(saved.vehicleType);
    if (saved.vehicleModelName) setVehicleModelName(saved.vehicleModelName);
    if (saved.plateNumber) setPlateNumber(saved.plateNumber);
    if (saved.contactPhone) setContactPhone(saved.contactPhone);
    if (saved.notes) setIssueNotes(saved.notes);
    if (saved.paymentMethod) setPaymentMethod(saved.paymentMethod);
    if (saved.serviceId) {
      const svc = SERVICES_DATA.find((s) => s.id === saved.serviceId);
      if (svc) setSelectedService(svc);
    }
  }, []);

  useEffect(() => {
    saveDispatchHistory(dispatchHistory);
  }, [dispatchHistory]);

  useEffect(() => {
    if (toastMessage) {
      const timer = setTimeout(() => setToastMessage(null), 3200);
      return () => clearTimeout(timer);
    }
  }, [toastMessage]);

  const setViewPin = (pin: string) => {
    const digits = pin.replace(/\D/g, '').slice(0, 4);
    setViewPinState(digits);
    if (digits.length === 4) saveViewPinLocal(digits);
  };

  const showToast = (msg: string) => setToastMessage(msg);
  const openModal = (id: string) => setActiveModal(id);
  const closeModal = () => setActiveModal(null);
  const openBottomSheet = (id: string) => setActiveBottomSheet(id);
  const closeBottomSheet = () => setActiveBottomSheet(null);
  const setCoordinates = (lat: number, lng: number) => {
    setLatitude(lat);
    setLongitude(lng);
  };

  const navigateTo = (screen: ScreenType) => {
    setCurrentScreen(screen);
    if (screen === 'home') setCurrentTab('home');
    if (screen === 'history') setCurrentTab('history');
    if (screen === 'requests') setCurrentTab('requests');
    if (screen === 'my') setCurrentTab('my');
  };

  const unlockWithPin = (pin: string) => {
    if (!verifyViewPin(pin, viewPin || loadSavedViewPin())) return false;
    setPinUnlocked(true);
    setPinUnlockedState(true);
    setPrivacyLocked(false);
    return true;
  };

  const lockPin = () => {
    setPinUnlocked(false);
    setPinUnlockedState(false);
    setPrivacyLocked(true);
  };

  const openDispatchById = (id: string) => {
    const found = dispatchHistory.find((d) => d.id === id);
    if (!found) return;
    setActiveDispatch(found);
    if (found.locationAddress) setLocationAddress(found.locationAddress);
    if (found.landmark) setLandmark(found.landmark);
    if (found.contactPhone) setContactPhone(found.contactPhone);
    if (found.serviceId) {
      const svc = SERVICES_DATA.find((s) => s.id === found.serviceId);
      if (svc) setSelectedService(svc);
    }
    setCurrentScreen('driver_assigned');
  };

  const clearFormFields = () => {
    setServiceCondition('');
    setVehicleModelName('');
    setPlateNumber('');
    setContactPhone('');
    setIssueNotes('');
    releaseUploadedPhotos(uploadedPhotos);
    setUploadedPhotos([]);
    setPaymentMethod('cash');
    setSelectedVehicleType('sedan');
    setViewPinState(DEFAULT_VIEW_PIN);
    saveViewPinLocal(DEFAULT_VIEW_PIN);
  };

  const wipeThisDevice = () => {
    wipeDeviceLocalData();
    setDispatchHistory([]);
    setActiveDispatch(null);
    clearFormFields();
    setPinUnlocked(false);
    setPinUnlockedState(false);
    setPrivacyLocked(false);
    closeModal();
    setCurrentScreen('home');
    setCurrentTab('home');
    showToast(
      lang === 'ko'
        ? '이 기기의 신청·연락처·차량 기록을 모두 삭제했습니다'
        : 'All request data wiped from this device'
    );
  };

  const resetForNewRequest = () => {
    setActiveDispatch(null);
    clearFormFields();
    closeModal();
    setCurrentScreen('home');
    setCurrentTab('home');
    showToast(
      lang === 'ko'
        ? '새 출동을 접수할 수 있습니다. 기존 건은 번호+비번으로 재조회 가능합니다'
        : 'Ready for a new request. Old ones stay recoverable via phone+PIN'
    );
  };

  const startFastRescue = (serviceId?: string) => {
    if (serviceId) {
      const target = SERVICES_DATA.find((s) => s.id === serviceId);
      if (target) setSelectedService(target);
    } else if (!selectedService) {
      setSelectedService(SERVICES_DATA[0]);
    }
    setCurrentScreen('location_confirm');
  };

  const submitDispatchRequest = async (): Promise<boolean> => {
    if (submitLockRef.current || isDispatchSubmitting) return false;
    const pin = (viewPin || DEFAULT_VIEW_PIN).padStart(4, '0').slice(0, 4);
    if (!/^\d{4}$/.test(pin)) {
      showToast(lang === 'ko' ? '조회 비밀번호 4자리를 입력해 주세요' : 'Enter a 4-digit PIN');
      return false;
    }
    const phoneNorm = normalizePhone(contactPhone || '');
    if (!isDispatchPhone(phoneNorm)) {
      showToast(
        lang === 'ko'
          ? '필리핀(09) 또는 한국(010) 번호를 입력해 주세요'
          : 'Enter a Philippine (09) or Korean (010) number'
      );
      return false;
    }
    if (isOnsiteLookupCode(phoneNorm)) {
      const place = landmark.trim();
      const plate = plateNumber.trim();
      if (place.length < 4 || plate.length < 4) {
        showToast(
          lang === 'ko'
            ? '현장 대기는 위치 설명과 차량 번호판이 필요합니다'
            : 'On-site wait needs a landmark and plate number'
        );
        return false;
      }
    }
    saveViewPinLocal(pin);
    setViewPinState(pin);

    submitLockRef.current = true;
    setIsDispatchSubmitting(true);
    setDispatchSubmitError(null);
    setDispatchUploadProgress(0);
    try {
      let newDispatch = pendingDispatchRef.current;
      if (!newDispatch) {
        const now = new Date();
        const yy = String(now.getFullYear()).slice(-2);
        const mm = String(now.getMonth() + 1).padStart(2, '0');
        const dd = String(now.getDate()).padStart(2, '0');
        const receiptNo =
          phoneNorm.length >= 4
            ? phoneNorm.slice(-4)
            : String(Math.floor(1000 + Math.random() * 9000));
        const baseId = `#CR${yy}${mm}${dd}-${receiptNo}`;
        let dispatchId = baseId;
        let dup = 2;
        while (dispatchHistory.some((d) => d.id === dispatchId)) {
          dispatchId = `${baseId}-${dup++}`;
        }
        newDispatch = {
          id: dispatchId,
          receiptNo,
          serviceId: selectedService?.id || 'battery',
          status: 'assigned',
          locationAddress,
          locationDetails,
          landmark,
          latitude,
          longitude,
          vehicleType: selectedVehicleType,
          vehicleModelName: vehicleModelName || '차종 정보',
          plateNumber: plateNumber || '번호판 미입력',
          contactPhone: contactPhone || '',
          notes: isOnsiteLookupCode(phoneNorm)
            ? `현장 대기. 전화 없이 위치와 번호판으로 찾아주세요. ${issueNotes}`.trim()
            : issueNotes,
          photos: [],
          paymentMethod,
          driver: undefined,
          customerPin: pin,
          adminCallStatus: 'pending',
          requestedAt: now.toLocaleString([], {
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
          }),
          createdAtMs: now.getTime(),
          etaMinutes: 14,
          estimatedCostPhp:
            (selectedService?.basePricePhp || 1500) +
            (selectedVehicleType === 'suv' ? 300 : selectedVehicleType === 'truck' ? 800 : 0),
        };
        pendingDispatchRef.current = newDispatch;
      }

      let res: Awaited<ReturnType<typeof saveDispatchRemote>>;
      try {
        res = await saveDispatchRemote(
          contactPhone,
          pin,
          newDispatch,
          uploadedPhotos,
          setDispatchUploadProgress
        );
      } catch {
        res = { ok: false, error: 'network' };
      }
      if (
        !res.ok &&
        ['invalid_server_response', 'network', 'timeout'].includes(res.error || '')
      ) {
        const lookup = await lookupDispatchRemote(contactPhone, pin);
        const found = lookup.items?.find((item) => item.id === newDispatch?.id);
        if (lookup.ok && found) {
          res = { ok: true, photos: found.photos || [], item: found };
        }
      }
      if (!res.ok) {
        const koMessages: Record<string, string> = {
          pin_mismatch: '이 번호는 기존 비밀번호가 있습니다. 같은 비번으로 다시 신청해 주세요.',
          too_many_photos: '사진은 최대 10장까지 첨부할 수 있습니다.',
          photo_too_large: '사진 용량이 너무 큽니다. 다른 사진을 선택해 주세요.',
          unsupported_photo_type: '지원하지 않는 사진 형식이 포함되어 있습니다.',
          upload_request_too_large: '전체 사진 용량이 너무 큽니다. 사진 수를 줄여 주세요.',
          timeout: '응답이 지연되었습니다. 다시 누르면 같은 접수번호로 확인합니다.',
          network: '서버 응답을 확인하지 못했습니다. 다시 누르면 같은 접수번호로 확인합니다.',
          invalid_server_response:
            '서버에는 접수되었을 수 있습니다. 다시 누르면 같은 접수번호로 확인하며 중복 접수되지 않습니다.',
          rate_limited: '요청이 너무 많아 잠시 제한되었습니다. 1~2분 후 다시 눌러 주세요.',
          invalid_phone: '필리핀(09) 또는 한국(010) 번호를 입력해 주세요.',
          onsite_details_required: '현장 대기는 위치 설명과 차량 번호판이 필요합니다.',
          invalid_pin: '조회 비밀번호 4자리를 다시 확인해 주세요.',
          save_failed: '서버 저장에 실패했습니다. 잠시 후 다시 시도해 주세요.',
        };
        const code = res.error || 'unknown';
        const message =
          lang === 'ko'
            ? `${koMessages[code] || '접수에 실패했습니다. 잠시 후 다시 시도해 주세요.'} (오류: ${code})`
            : code === 'pin_mismatch'
              ? 'This phone already has a PIN. Please use the same PIN.'
              : `Request failed (${code}). Retry uses the same request number.`;
        setDispatchSubmitError(message);
        showToast(message);
        return false;
      }

      const savedDispatch = {
        ...newDispatch,
        ...(res.item || {}),
        id: newDispatch.id,
        customerPin: pin,
        photos: res.photos || res.item?.photos || [],
      };
      setDispatchHistory((prev) => upsertDispatch(prev, savedDispatch));
      setActiveDispatch(savedDispatch);
      setPinUnlocked(true);
      setPinUnlockedState(true);
      setPrivacyLocked(false);
      releaseUploadedPhotos(uploadedPhotos);
      setUploadedPhotos([]);
      setDispatchUploadProgress(100);
      pendingDispatchRef.current = null;
      setCurrentScreen('driver_assigned');
      showToast(
        lang === 'ko'
          ? '출동요청 접수완료'
          : 'Dispatch request received'
      );
      return true;
    } finally {
      submitLockRef.current = false;
      setIsDispatchSubmitting(false);
    }
  };

  const lookupRemoteRequests = async (phone: string, pin: string) => {
    const res = await lookupDispatchRemote(phone, pin);
    if (!res.ok) {
      const msg =
        res.error === 'pin_mismatch'
          ? lang === 'ko'
            ? '비밀번호가 올바르지 않습니다'
            : 'Incorrect PIN'
          : res.error === 'not_found'
            ? lang === 'ko'
              ? '해당 번호로 저장된 신청이 없습니다'
              : 'No requests found for this phone'
            : lang === 'ko'
              ? '조회에 실패했습니다. 네트워크를 확인해 주세요'
              : 'Lookup failed. Check your connection';
      showToast(msg);
      return { ok: false, error: res.error };
    }
    const items = res.items || [];
    setDispatchHistory((prev) => {
      let next = [...prev];
      items.forEach((item) => {
        next = upsertDispatch(next, item);
      });
      return next;
    });
    saveViewPinLocal(pin);
    setViewPinState(pin);
    setContactPhone(phone);
    setPinUnlocked(true);
    setPinUnlockedState(true);
    setPrivacyLocked(false);
    const latest = getLatestOpenDispatch(items) || items[0] || null;
    if (latest) {
      setActiveDispatch(latest);
      if (latest.locationAddress) setLocationAddress(latest.locationAddress);
      if (latest.landmark) setLandmark(latest.landmark || '');
      if (latest.serviceId) {
        const svc = SERVICES_DATA.find((s) => s.id === latest.serviceId);
        if (svc) setSelectedService(svc);
      }
      setCurrentScreen('driver_assigned');
    }
    showToast(
      lang === 'ko'
        ? `${items.length}건을 이 폰으로 불러왔습니다`
        : `Synced ${items.length} request(s) to this phone`
    );
    return { ok: true };
  };

  const refreshAdminDispatches = async (sessionToken = adminToken) => {
    if (!sessionToken) return;
    const res = await adminListDispatches(sessionToken);
    if (!res.ok && res.error === 'admin_session_required') {
      clearAdminSession();
      clearDeviceAdmin();
      setAdminToken('');
      setAdminUser(null);
      setIsAdminAuthenticated(false);
      return;
    }
    const remote = res.ok && res.items ? res.items : [];
    setAllDispatches((prevLocal) => {
      const local = dispatchHistory;
      const map = new Map<string, DispatchRequest>();
      [...local, ...prevLocal, ...remote].forEach((item) => {
        if (item?.id) map.set(item.id, { ...map.get(item.id), ...item });
      });
      return Array.from(map.values()).sort(
        (a, b) => (b.createdAtMs || 0) - (a.createdAtMs || 0)
      );
    });
  };

  const loadAdminPhoto = async (photoId: string): Promise<string | null> => {
    if (!adminToken) return null;
    const res = await adminFetchPhoto(adminToken, photoId);
    return res.ok && res.blob ? URL.createObjectURL(res.blob) : null;
  };

  const loginAdmin = async (adminId: string, password: string) => {
    const res = await adminLoginRemote(adminId.trim(), password);
    if (!res.ok || !res.token || !res.admin) {
      return { ok: false, error: res.error };
    }
    saveAdminSession({ token: res.token, admin: res.admin });
    setAdminToken(res.token);
    setAdminUser(res.admin);
    setIsAdminAuthenticated(true);
    markDeviceAsAdmin();
    void refreshAdminDispatches(res.token);
    return { ok: true };
  };

  const logoutAdmin = () => {
    const token = adminToken;
    clearAdminSession();
    clearDeviceAdmin();
    setAdminToken('');
    setAdminUser(null);
    setIsAdminAuthenticated(false);
    setAllDispatches([]);
    setCurrentScreen('home');
    setCurrentTab('home');
    if (token) void adminLogoutRemote(token);
  };

  const registerAdmin = async (adminId: string, password: string, name: string) => {
    if (!adminToken) return { ok: false, error: 'admin_session_required' };
    const res = await adminRegisterRemote(adminToken, adminId.trim(), password, name.trim());
    return { ok: res.ok, requiresLogin: res.bootstrapReplaced, error: res.error };
  };

  const listAdminAccounts = async () => {
    if (!adminToken) return { ok: false, error: 'admin_session_required' };
    return adminListAccountsRemote(adminToken);
  };

  const deleteAdminAccount = async (adminId: string) => {
    if (!adminToken) return { ok: false, error: 'admin_session_required' };
    return adminDeleteAccountRemote(adminToken, adminId);
  };

  const changeAdminPassword = async (currentPassword: string, newPassword: string) => {
    if (!adminToken) return { ok: false, error: 'admin_session_required' };
    return adminChangePasswordRemote(adminToken, currentPassword, newPassword);
  };

  const resetAdminPassword = async (adminId: string, newPassword: string) => {
    if (!adminToken) return { ok: false, error: 'admin_session_required' };
    return adminResetPasswordRemote(adminToken, adminId, newPassword);
  };

  const updateDispatchAdminStatus = (
    id: string,
    status: 'pending' | 'contacted' | 'completed'
  ) => {
    setAllDispatches((prev) =>
      prev.map((item) =>
        item.id === id
          ? {
              ...item,
              adminCallStatus: status,
              handledBy: status === 'pending' ? undefined : adminUser?.id,
            }
          : item
      )
    );
    setDispatchHistory((prev) =>
      prev.map((item) =>
        item.id === id
          ? {
              ...item,
              adminCallStatus: status,
              handledBy: status === 'pending' ? undefined : adminUser?.id,
            }
          : item
      )
    );
    if (adminToken) {
      void adminSetStatus(adminToken, id, status).then(() => {
        markDeviceAsAdmin();
        window.setTimeout(markDeviceAsAdmin, 16000);
      });
    }
    showToast(lang === 'ko' ? '상태가 업데이트되었습니다.' : 'Status updated.');
  };

  const cancelDispatchRequest = () => {
    const currentId = activeDispatch?.id;
    setDispatchHistory((prev) => {
      const nextList = currentId
        ? upsertDispatch(prev, {
            ...(prev.find((d) => d.id === currentId) || activeDispatch!),
            status: 'cancelled',
          })
        : prev;
      setActiveDispatch(getLatestOpenDispatch(nextList));
      return nextList;
    });
    closeModal();
    setCurrentScreen('home');
    showToast(lang === 'ko' ? '출동 요청이 취소되었습니다.' : 'Rescue request cancelled.');
  };

  const completeCurrentRescue = () => {
    if (!activeDispatch) {
      setCurrentScreen('service_complete');
      return;
    }
    setDispatchHistory((prev) => {
      const nextList = upsertDispatch(prev, { ...activeDispatch, status: 'completed' });
      setActiveDispatch(getLatestOpenDispatch(nextList));
      return nextList;
    });
    setCurrentScreen('service_complete');
  };

  return (
    <AppContext.Provider
      value={{
        lang,
        setLang,
        currentScreen,
        navigateTo,
        currentTab,
        setCurrentTab,
        selectedService,
        setSelectedService,
        serviceCondition,
        setServiceCondition,
        activeDispatch,
        setActiveDispatch,
        dispatchHistory,
        openDispatchById,
        viewPin,
        setViewPin,
        pinUnlocked,
        unlockWithPin,
        lockPin,
        privacyLocked,
        setPrivacyLocked,
        locationAddress,
        setLocationAddress,
        locationDetails,
        setLocationDetails,
        landmark,
        setLandmark,
        latitude,
        longitude,
        setCoordinates,
        selectedVehicleType,
        setSelectedVehicleType,
        vehicleModelName,
        setVehicleModelName,
        plateNumber,
        setPlateNumber,
        contactPhone,
        setContactPhone,
        issueNotes,
        setIssueNotes,
        uploadedPhotos,
        setUploadedPhotos,
        isDispatchSubmitting,
        dispatchUploadProgress,
        dispatchSubmitError,
        clearDispatchSubmitError: () => setDispatchSubmitError(null),
        paymentMethod,
        setPaymentMethod,
        toastMessage,
        showToast,
        activeModal,
        openModal,
        closeModal,
        activeBottomSheet,
        openBottomSheet,
        closeBottomSheet,
        startFastRescue,
        submitDispatchRequest,
        cancelDispatchRequest,
        completeCurrentRescue,
        lookupRemoteRequests,
        wipeThisDevice,
        resetForNewRequest,
        isAdminAuthenticated,
        adminUser,
        allDispatches,
        loginAdmin,
        logoutAdmin,
        registerAdmin,
        listAdminAccounts,
        deleteAdminAccount,
        changeAdminPassword,
        resetAdminPassword,
        loadAdminPhoto,
        refreshAdminDispatches,
        updateDispatchAdminStatus,
      }}
    >
      {children}
    </AppContext.Provider>
  );
};

export const useApp = () => {
  const context = useContext(AppContext);
  if (!context) {
    throw new Error('useApp must be used within an AppProvider');
  }
  return context;
};
