export type Language = 'ko' | 'en';

export type ScreenType =
  | 'splash'
  | 'home'
  | 'service_select'
  | 'service_detail'
  | 'location_confirm'
  | 'request_info'
  | 'request_confirm'
  | 'dispatch_searching'
  | 'driver_assigned'
  | 'driver_tracking'
  | 'service_complete'
  | 'history'
  | 'history_detail'
  | 'requests'
  | 'my'
  | 'vehicle_info'
  | 'support'
  | 'admin_login'
  | 'admin_dashboard'
  | 'admin_stats'
  | 'admin_register';

export interface AdminUser {
  id: string;
  name: string;
  role: 'owner' | 'admin';
  createdAt?: string;
}

export interface UploadedPhoto {
  id: string;
  file: File;
  previewUrl: string;
  originalName: string;
}

export type TabType = 'home' | 'history' | 'requests' | 'my';

export type ServiceId =
  | 'battery'
  | 'tire'
  | 'towing'
  | 'lockout'
  | 'fuel'
  | 'accident'
  | 'sos';

export interface ServiceItem {
  id: ServiceId;
  nameKo: string;
  nameEn: string;
  shortDescKo: string;
  shortDescEn: string;
  detailKo: string;
  detailEn: string;
  basePricePhp: number;
  priceDisplay: string;
  estimatedMinutes: string;
  iconName: string;
  popular?: boolean;
  emergency?: boolean;
}

export type VehicleTypeId = 'sedan' | 'suv' | 'van' | 'pickup' | 'truck' | 'other';

export interface VehicleTypeOption {
  id: VehicleTypeId;
  nameKo: string;
  nameEn: string;
  iconName: string;
  surchargePhp: number;
}

export type PaymentMethodId = 'cash' | 'gcash' | 'kr_transfer' | 'card';

export interface PaymentMethodOption {
  id: PaymentMethodId;
  nameKo: string;
  nameEn: string;
  descriptionKo: string;
  descriptionEn: string;
  iconName: string;
  badge?: string;
}

export interface DriverInfo {
  id: string;
  name: string;
  phone: string;
  rating: number;
  completedRescues: number;
  vehicleModel: string;
  vehiclePlate: string;
  photoUrl: string;
  latitude: number;
  longitude: number;
  currentAddress: string;
}

export type DispatchStatus =
  | 'searching'
  | 'assigned'
  | 'moving'
  | 'arrived'
  | 'in_progress'
  | 'completed'
  | 'cancelled';

export interface DispatchRequest {
  id: string;
  /** Shown to customer as 접수번호 (phone last 4 digits) */
  receiptNo?: string;
  serviceId: ServiceId;
  status: DispatchStatus;
  locationAddress: string;
  locationDetails?: string;
  landmark?: string;
  latitude: number;
  longitude: number;
  vehicleType: VehicleTypeId;
  vehicleModelName: string;
  plateNumber: string;
  contactPhone: string;
  /** On-site wait only. kakao or facebook. */
  messengerType?: 'kakao' | 'facebook' | '';
  messengerId?: string;
  contactChannel?: 'onsite' | '';
  notes?: string;
  photos: string[];
  paymentMethod: PaymentMethodId;
  driver?: DriverInfo;
  requestedAt: string;
  /** Sort key for multi-request history on this phone */
  createdAtMs?: number;
  etaMinutes: number;
  estimatedCostPhp: number;
  customerPin?: string;
  adminCallStatus?: 'pending' | 'contacted' | 'completed';
  handledBy?: string;
}

export interface HistoryItem {
  id: string;
  serviceId: ServiceId;
  serviceNameKo: string;
  serviceNameEn: string;
  date: string;
  address: string;
  vehicleModel: string;
  plateNumber: string;
  costPhp: number;
  status: 'completed' | 'cancelled';
  driverName: string;
  rating?: number;
}
