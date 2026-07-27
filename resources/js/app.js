import './bootstrap';
import Alpine from 'alpinejs';
import { registerCart } from './cart';

// Register the multi-vendor cart store before Alpine starts.
registerCart(Alpine);

window.Alpine = Alpine;
Alpine.start();
