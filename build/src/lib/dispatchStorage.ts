import { DispatchRequest } from '../types';

const HISTORY_KEY = 'cebu24_dispatch_history_v1';
const LEGACY_KEY = 'cebu24_active_dispatch_v1';
const PIN_SESSION_KEY = 'cebu24_pin_unlocked_v1';
const VIEW_PIN_KEY = 'cebu24_view_pin_v1';
const FORM_CACHE_KEYS = [
  'cebu24_form_phone',
  'cebu24_form_plate',
  'cebu24_form_model',
  'cebu24_contact_profile_v1',
] as const;

export type ContactMode = 'ph' | 'kr' | 'onsite';

export interface ContactProfile {
  mode: ContactMode;
  phone: string;
  messengerType: '' | 'kakao' | 'facebook';
  messengerId: string;
}

const EMPTY_CONTACT_PROFILE: ContactProfile = {
  mode: 'ph',
  phone: '',
  messengerType: '',
  messengerId: '',
};

export function loadContactProfile(): ContactProfile {
  try {
    const raw = localStorage.getItem('cebu24_contact_profile_v1');
    if (!raw) return EMPTY_CONTACT_PROFILE;
    const data = JSON.parse(raw) as Partial<ContactProfile>;
    const mode = data.mode === 'kr' || data.mode === 'onsite' || data.mode === 'ph' ? data.mode : 'ph';
    const messengerType = data.messengerType === 'kakao' || data.messengerType === 'facebook' ? data.messengerType : '';
    return {
      mode,
      phone: typeof data.phone === 'string' ? data.phone.slice(0, 20) : '',
      messengerType,
      messengerId: typeof data.messengerId === 'string' ? data.messengerId.slice(0, 80) : '',
    };
  } catch {
    return EMPTY_CONTACT_PROFILE;
  }
}

export function saveContactProfile(profile: ContactProfile): void {
  try {
    localStorage.setItem('cebu24_contact_profile_v1', JSON.stringify(profile));
  } catch {
    /* ignore quota / private mode */
  }
}

/** Default inquiry / security PIN set at request time */
export const DEFAULT_VIEW_PIN = '1234';

function sanitize(d: DispatchRequest): DispatchRequest {
  return {
    ...d,
    photos: Array.isArray(d.photos) ? [] : [],
    driver: undefined,
  };
}

function isOpen(d: DispatchRequest): boolean {
  return !['completed', 'cancelled'].includes(d.status);
}

export function loadDispatchHistory(): DispatchRequest[] {
  try {
    const raw = localStorage.getItem(HISTORY_KEY);
    if (raw) {
      const list = JSON.parse(raw) as DispatchRequest[];
      if (Array.isArray(list)) {
        return list
          .filter((d) => d?.id && d?.status)
          .map(sanitize)
          .sort((a, b) => (b.createdAtMs || 0) - (a.createdAtMs || 0));
      }
    }

    const legacy = localStorage.getItem(LEGACY_KEY);
    if (legacy) {
      const one = JSON.parse(legacy) as DispatchRequest;
      if (one?.id && one?.status) {
        const migrated = [
          sanitize({
            ...one,
            createdAtMs: one.createdAtMs || Date.now(),
          }),
        ];
        localStorage.setItem(HISTORY_KEY, JSON.stringify(migrated));
        localStorage.removeItem(LEGACY_KEY);
        return migrated;
      }
    }
  } catch {
    /* ignore */
  }
  return [];
}

export function saveDispatchHistory(list: DispatchRequest[]): void {
  try {
    const cleaned = list.map(sanitize).slice(0, 30);
    localStorage.setItem(HISTORY_KEY, JSON.stringify(cleaned));
    const latestOpen = cleaned.find(isOpen) || null;
    if (latestOpen) {
      localStorage.setItem(LEGACY_KEY, JSON.stringify(latestOpen));
    } else {
      localStorage.removeItem(LEGACY_KEY);
    }
  } catch {
    /* ignore */
  }
}

export function getLatestOpenDispatch(list: DispatchRequest[]): DispatchRequest | null {
  return list.find(isOpen) || null;
}

export function upsertDispatch(
  list: DispatchRequest[],
  item: DispatchRequest
): DispatchRequest[] {
  const next = [sanitize(item), ...list.filter((d) => d.id !== item.id)];
  return next.sort((a, b) => (b.createdAtMs || 0) - (a.createdAtMs || 0));
}

/** Wipe all local request/PII data on this browser (borrowed phone) */
export function wipeDeviceLocalData(): void {
  try {
    localStorage.removeItem(HISTORY_KEY);
    localStorage.removeItem(LEGACY_KEY);
    localStorage.removeItem(VIEW_PIN_KEY);
    FORM_CACHE_KEYS.forEach((k) => localStorage.removeItem(k));
    sessionStorage.removeItem(PIN_SESSION_KEY);
    // Clear any leftover cebu24 keys
    const toRemove: string[] = [];
    for (let i = 0; i < localStorage.length; i++) {
      const k = localStorage.key(i);
      if (k && k.startsWith('cebu24_')) toRemove.push(k);
    }
    toRemove.forEach((k) => localStorage.removeItem(k));
  } catch {
    /* ignore */
  }
}

export function loadSavedViewPin(): string {
  try {
    return localStorage.getItem(VIEW_PIN_KEY) || DEFAULT_VIEW_PIN;
  } catch {
    return DEFAULT_VIEW_PIN;
  }
}

export function saveViewPinLocal(pin: string): void {
  try {
    if (/^\d{4}$/.test(pin)) localStorage.setItem(VIEW_PIN_KEY, pin);
  } catch {
    /* ignore */
  }
}

export function isPinUnlocked(): boolean {
  try {
    return sessionStorage.getItem(PIN_SESSION_KEY) === '1';
  } catch {
    return false;
  }
}

export function setPinUnlocked(unlocked: boolean): void {
  try {
    if (unlocked) sessionStorage.setItem(PIN_SESSION_KEY, '1');
    else sessionStorage.removeItem(PIN_SESSION_KEY);
  } catch {
    /* ignore */
  }
}

export function verifyViewPin(pin: string, expected?: string): boolean {
  const target = expected || loadSavedViewPin() || DEFAULT_VIEW_PIN;
  return pin === target;
}
