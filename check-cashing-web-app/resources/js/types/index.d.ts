import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export type CustomerForm = {
    first_name: string;
    middle_initial: string;
    last_name: string;
    address_street: string;
    address_city: string;
    address_state: string;
    address_zip: string;
    phone_number: string;
    dl_number: string;
    dl_state: string;
    dob: Date | null;
    dl_picture_link: File | null;
    self_picture_link: File | null;
    notes: string
    _method?: string;
}

export type Customer = {
    id: number;
    first_name: string;
    middle_initial: string;
    last_name: string;
    address_street: string;
    address_city: string;
    address_state: string;
    address_zip: string;
    phone_number: string;
    dl_number: string;
    dl_state: string;
    dob: Date | null;
    dl_picture_link: File | null;
    self_picture_link: File | null;
    notes: string
    slug: string;
}

export type CheckForm = {
    company_name: string;
    company_address_street: string;
    company_address_city: string;
    company_address_state: string;
    company_address_zip: string;
    account_number: string;
    routing_number: string;
    type: string;
    cashing_status: string;
    notes: string
    _method?: string;
}

export type Check = {
    id: number;
    company_name: string;
    company_address_street: string;
    company_address_city: string;
    company_address_state: string;
    company_address_zip: string;    
    account_number: string;
    routing_number: string;
    type: string;
    cashing_status: string;
    notes: string
    slug: string;
}

export type TransactionForm = {
    customer_id: number;
    check_id: number;
    date: Date | null;
    check_amount: string;
    payout_amount: string;
    charge_amount: string;
    charge_percentage: string;
    check_number: string;
    check_picture_link: File | null;
    _method?: string;
}

export type Transaction = {
    id: number;
    customer_id: number;
    check_id: number;
    date: Date | null;
    check_amount: string;
    payout_amount: string;
    charge_amount: string;
    charge_percentage: string;
    check_number: string;
    check_picture_link: File | null;
    slug: string;
}