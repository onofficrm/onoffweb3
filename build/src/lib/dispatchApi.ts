import { AdminUser, DispatchRequest, UploadedPhoto } from '../types';

declare global {
  interface Window {
    __CEBU24_RUNTIME__?: {
      googleMapsApiKey?: string;
      dispatchApiUrl?: string;
    };
  }
}

export function getDispatchApiUrl(): string {
  const fromRuntime = window.__CEBU24_RUNTIME__?.dispatchApiUrl;
  if (fromRuntime) return fromRuntime;
  return '/plugin/onoff-builder-bridge/api/dispatch.php';
}

function getDispatchPhotoApiUrl(): string {
  return getDispatchApiUrl().replace(/dispatch\.php(?:\?.*)?$/, 'dispatch-photo.php');
}

export type PhoneCountry = 'ph' | 'kr' | 'onsite';

export function normalizePhone(phone: string): string {
  let digits = phone.replace(/\D/g, '');
  if (digits.startsWith('82')) {
    if (digits.length === 12) digits = '0' + digits.slice(2);
    else if (digits.length === 13 && digits.startsWith('820')) digits = digits.slice(2);
  }
  if (digits.length === 12 && digits.startsWith('63')) {
    digits = '0' + digits.slice(2);
  }
  if (digits.length === 10 && digits.startsWith('9')) {
    digits = '0' + digits;
  }
  if (digits.length === 10 && digits.startsWith('10')) {
    digits = '0' + digits;
  }
  return digits;
}

export function isPhilippinePhone(phone: string): boolean {
  return /^09\d{9}$/.test(normalizePhone(phone));
}

export function isKoreanPhone(phone: string): boolean {
  return /^010\d{8}$/.test(normalizePhone(phone));
}

export function isCallablePhone(phone: string): boolean {
  return isPhilippinePhone(phone) || isKoreanPhone(phone);
}

/** Lookup-only code for customers waiting on site with no reachable number. */
export function isOnsiteLookupCode(phone: string): boolean {
  return /^900\d{8}$/.test(normalizePhone(phone));
}

export function isDispatchPhone(phone: string): boolean {
  return isCallablePhone(phone) || isOnsiteLookupCode(phone);
}

export function formatPhoneInput(value: string, country: 'ph' | 'kr'): string {
  const raw = value.replace(/\D/g, '').slice(0, 11);
  if (country === 'kr') {
    if (raw.length > 7) return `${raw.slice(0, 3)}-${raw.slice(3, 7)}-${raw.slice(7)}`;
    if (raw.length > 3) return `${raw.slice(0, 3)}-${raw.slice(3)}`;
    return raw;
  }
  if (raw.length > 7) return `${raw.slice(0, 4)} ${raw.slice(4, 7)} ${raw.slice(7)}`;
  if (raw.length > 4) return `${raw.slice(0, 4)} ${raw.slice(4)}`;
  return raw;
}

export function createOnsiteLookupCode(): string {
  const tail = String(Math.floor(10000000 + Math.random() * 90000000));
  return formatPhoneInput(`900${tail}`, 'ph');
}

/** Customer-facing receipt number: last 4 digits of contact phone */
export function receiptNoFromPhone(phone: string): string {
  const digits = normalizePhone(phone);
  if (digits.length < 4) return '----';
  return digits.slice(-4);
}

function yymmddFromMs(ms?: number): string {
  const d = ms ? new Date(ms) : new Date();
  if (Number.isNaN(d.getTime())) return '';
  const yy = String(d.getFullYear()).slice(-2);
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${yy}${mm}${dd}`;
}

/**
 * Display 접수번호: #CR + date + phone last 4
 * CR = Cebu Rescue
 */
export function displayReceiptNo(item: {
  id?: string;
  receiptNo?: string;
  contactPhone?: string;
  createdAtMs?: number;
}): string {
  const id = item.id || '';
  const last4 =
    item.receiptNo && /^\d{4}$/.test(item.receiptNo)
      ? item.receiptNo
      : item.contactPhone
        ? receiptNoFromPhone(item.contactPhone)
        : '----';
  const dateFromId = (id.match(/^#CR(\d{6})-/) || [])[1] || '';
  const datePart = dateFromId || yymmddFromMs(item.createdAtMs);

  // Keep full id when it already uses phone last 4 (incl. -2 duplicates)
  if (last4 !== '----' && id.startsWith('#CR') && id.includes(`-${last4}`)) {
    return id;
  }
  if (last4 !== '----' && datePart) {
    return `#CR${datePart}-${last4}`;
  }
  if (id.startsWith('#CR')) return id;
  return last4 !== '----' ? last4 : id || '----';
}

function stripForServer(item: DispatchRequest): DispatchRequest {
  return {
    ...item,
    photos: [],
    driver: undefined,
  };
}

export function parseDispatchSaveBody(
  status: number,
  raw: string
): { ok: boolean; photos?: string[]; item?: DispatchRequest; error?: string } {
  const trimmed = raw.replace(/^\uFEFF/, '').trim();
  let data: {
    ok?: boolean;
    photos?: string[];
    item?: DispatchRequest;
    error?: string;
  } | null = null;
  try {
    data = JSON.parse(trimmed) as typeof data;
  } catch {
    const start = trimmed.indexOf('{');
    const end = trimmed.lastIndexOf('}');
    if (start >= 0 && end > start) {
      try {
        data = JSON.parse(trimmed.slice(start, end + 1)) as typeof data;
      } catch {
        data = null;
      }
    }
  }
  if (!data || typeof data !== 'object') {
    return { ok: false, error: 'invalid_server_response' };
  }
  const httpOk = status === 0 || (status >= 200 && status < 300);
  if (!httpOk || !data.ok) {
    return { ok: false, error: data.error || 'save_failed' };
  }
  const photos = Array.isArray(data.photos)
    ? data.photos
    : Array.isArray(data.item?.photos)
      ? data.item.photos
      : [];
  return { ok: true, photos, item: data.item };
}

export async function saveDispatchRemote(
  phone: string,
  pin: string,
  item: DispatchRequest,
  photos: UploadedPhoto[] = [],
  onProgress?: (percent: number) => void
): Promise<{ ok: boolean; photos?: string[]; item?: DispatchRequest; error?: string }> {
  const form = new FormData();
  form.append('action', 'save_with_photos');
  form.append('phone', normalizePhone(phone));
  form.append('pin', pin);
  form.append('item', JSON.stringify(stripForServer(item)));
  photos.slice(0, 10).forEach((photo) => {
    form.append('photos[]', photo.file, photo.file.name);
  });

  return new Promise((resolve) => {
    const xhr = new XMLHttpRequest();
    let settled = false;
    const finish = (result: { ok: boolean; photos?: string[]; item?: DispatchRequest; error?: string }) => {
      if (settled) return;
      settled = true;
      resolve(result);
    };
    xhr.open('POST', getDispatchApiUrl());
    xhr.timeout = 120000;
    xhr.upload.onprogress = (event) => {
      if (!event.lengthComputable) return;
      onProgress?.(Math.min(99, Math.round((event.loaded / event.total) * 100)));
    };
    xhr.onerror = () => finish({ ok: false, error: 'network' });
    xhr.ontimeout = () => finish({ ok: false, error: 'timeout' });
    xhr.onload = () => {
      const parsed = parseDispatchSaveBody(xhr.status, xhr.responseText || '');
      if (parsed.ok) onProgress?.(100);
      finish(parsed);
    };
    xhr.onloadend = () => {
      if (settled) return;
      const parsed = parseDispatchSaveBody(xhr.status, xhr.responseText || '');
      if (parsed.ok) onProgress?.(100);
      finish(parsed.ok ? parsed : { ok: false, error: parsed.error || 'network' });
    };
    xhr.send(form);
  });
}

export async function listPublicDispatches(
  phone?: string,
  pin?: string
): Promise<{
  ok: boolean;
  items?: Array<{
    id: string;
    receiptNo?: string;
    serviceId: string;
    status: string;
    requestedAt: string;
    createdAtMs?: number;
    updatedAtMs?: number;
    pending?: boolean;
    area: string;
    mine?: boolean;
  }>;
  error?: string;
}> {
  try {
    const res = await fetch(getDispatchApiUrl(), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'list',
        phone: phone ? normalizePhone(phone) : '',
        pin: pin || '',
      }),
    });
    const data = (await res.json()) as {
      ok?: boolean;
      items?: Array<{
        id: string;
        serviceId: string;
        status: string;
        requestedAt: string;
        createdAtMs?: number;
        updatedAtMs?: number;
        pending?: boolean;
        area: string;
        mine?: boolean;
      }>;
      error?: string;
    };
    if (!res.ok || !data.ok) {
      return { ok: false, error: data.error || 'list_failed' };
    }
    return { ok: true, items: Array.isArray(data.items) ? data.items : [] };
  } catch {
    return { ok: false, error: 'network' };
  }
}

export async function lookupDispatchRemote(
  phone: string,
  pin: string
): Promise<{ ok: boolean; items?: DispatchRequest[]; error?: string }> {
  try {
    const res = await fetch(getDispatchApiUrl(), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'lookup',
        phone: normalizePhone(phone),
        pin,
      }),
    });
    const data = (await res.json()) as {
      ok?: boolean;
      items?: DispatchRequest[];
      error?: string;
    };
    if (!res.ok || !data.ok) {
      return { ok: false, error: data.error || 'lookup_failed' };
    }
    return { ok: true, items: Array.isArray(data.items) ? data.items : [] };
  } catch {
    return { ok: false, error: 'network' };
  }
}

export async function adminLogin(
  adminId: string,
  password: string
): Promise<{ ok: boolean; token?: string; admin?: AdminUser; error?: string }> {
  try {
    const res = await fetch(getDispatchApiUrl(), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'admin_login', adminId, password }),
    });
    const data = (await res.json()) as {
      ok?: boolean;
      token?: string;
      admin?: AdminUser;
      error?: string;
    };
    if (!res.ok || !data.ok || !data.token || !data.admin) {
      return { ok: false, error: data.error || 'admin_login_failed' };
    }
    return { ok: true, token: data.token, admin: data.admin };
  } catch {
    return { ok: false, error: 'network' };
  }
}

export async function adminMe(
  token: string
): Promise<{ ok: boolean; admin?: AdminUser; error?: string }> {
  try {
    const res = await fetch(getDispatchApiUrl(), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'admin_me', token }),
    });
    const data = (await res.json()) as { ok?: boolean; admin?: AdminUser; error?: string };
    if (!res.ok || !data.ok || !data.admin) {
      return { ok: false, error: data.error || 'admin_session_required' };
    }
    return { ok: true, admin: data.admin };
  } catch {
    return { ok: false, error: 'network' };
  }
}

export async function adminLogout(token: string): Promise<void> {
  try {
    await fetch(getDispatchApiUrl(), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'admin_logout', token }),
    });
  } catch {
    // Local/native logout must still complete if the network is unavailable.
  }
}

export async function adminRegister(
  token: string,
  adminId: string,
  password: string,
  name: string
): Promise<{ ok: boolean; admin?: AdminUser; bootstrapReplaced?: boolean; error?: string }> {
  try {
    const res = await fetch(getDispatchApiUrl(), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'admin_register', token, adminId, password, name }),
    });
    const data = (await res.json()) as {
      ok?: boolean;
      admin?: AdminUser;
      bootstrapReplaced?: boolean;
      error?: string;
    };
    if (!res.ok || !data.ok) return { ok: false, error: data.error || 'admin_register_failed' };
    return {
      ok: true,
      admin: data.admin,
      bootstrapReplaced: !!data.bootstrapReplaced,
    };
  } catch {
    return { ok: false, error: 'network' };
  }
}

export async function adminListAccounts(
  token: string
): Promise<{ ok: boolean; items?: AdminUser[]; error?: string }> {
  try {
    const res = await fetch(getDispatchApiUrl(), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'admin_accounts', token }),
    });
    const data = (await res.json()) as { ok?: boolean; items?: AdminUser[]; error?: string };
    if (!res.ok || !data.ok) return { ok: false, error: data.error || 'admin_accounts_failed' };
    return { ok: true, items: Array.isArray(data.items) ? data.items : [] };
  } catch {
    return { ok: false, error: 'network' };
  }
}

export async function adminDeleteAccount(
  token: string,
  adminId: string
): Promise<{ ok: boolean; error?: string }> {
  try {
    const res = await fetch(getDispatchApiUrl(), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'admin_delete', token, adminId }),
    });
    const data = (await res.json()) as { ok?: boolean; error?: string };
    if (!res.ok || !data.ok) return { ok: false, error: data.error || 'admin_delete_failed' };
    return { ok: true };
  } catch {
    return { ok: false, error: 'network' };
  }
}

export async function adminChangePassword(
  token: string,
  currentPassword: string,
  newPassword: string
): Promise<{ ok: boolean; error?: string }> {
  try {
    const res = await fetch(getDispatchApiUrl(), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'admin_change_password',
        token,
        currentPassword,
        newPassword,
      }),
    });
    const data = (await res.json()) as { ok?: boolean; error?: string };
    if (!res.ok || !data.ok) return { ok: false, error: data.error || 'password_change_failed' };
    return { ok: true };
  } catch {
    return { ok: false, error: 'network' };
  }
}

export async function adminResetPassword(
  token: string,
  adminId: string,
  newPassword: string
): Promise<{ ok: boolean; error?: string }> {
  try {
    const res = await fetch(getDispatchApiUrl(), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'admin_reset_password', token, adminId, newPassword }),
    });
    const data = (await res.json()) as { ok?: boolean; error?: string };
    if (!res.ok || !data.ok) return { ok: false, error: data.error || 'password_reset_failed' };
    return { ok: true };
  } catch {
    return { ok: false, error: 'network' };
  }
}

export async function adminListDispatches(token: string): Promise<{
  ok: boolean;
  items?: DispatchRequest[];
  admin?: AdminUser;
  error?: string;
}> {
  try {
    const res = await fetch(getDispatchApiUrl(), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'admin_list', token }),
    });
    const data = (await res.json()) as {
      ok?: boolean;
      items?: DispatchRequest[];
      admin?: AdminUser;
      error?: string;
    };
    if (!res.ok || !data.ok) return { ok: false, error: data.error || 'admin_list_failed' };
    return { ok: true, items: data.items || [], admin: data.admin };
  } catch {
    return { ok: false, error: 'network' };
  }
}

export async function adminFetchPhoto(
  token: string,
  photoId: string
): Promise<{ ok: boolean; blob?: Blob; error?: string }> {
  try {
    const res = await fetch(getDispatchPhotoApiUrl(), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token, photoId }),
    });
    if (!res.ok) {
      const data = (await res.json().catch(() => ({}))) as { error?: string };
      return { ok: false, error: data.error || 'photo_load_failed' };
    }
    const blob = await res.blob();
    if (!blob.type.startsWith('image/')) return { ok: false, error: 'invalid_photo' };
    return { ok: true, blob };
  } catch {
    return { ok: false, error: 'network' };
  }
}

export async function adminSetStatus(
  token: string,
  id: string,
  status: 'pending' | 'contacted' | 'completed'
): Promise<{ ok: boolean; handledBy?: string }> {
  try {
    const res = await fetch(getDispatchApiUrl(), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'admin_status', token, id, status }),
    });
    const data = (await res.json()) as { ok?: boolean; handledBy?: string };
    return { ok: !!data.ok && res.ok, handledBy: data.handledBy };
  } catch {
    return { ok: false };
  }
}
