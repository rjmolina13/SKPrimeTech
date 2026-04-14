import './bootstrap';
import * as docx from 'docx-preview';
import * as XLSX from 'xlsx';
import { injectSpeedInsights } from '@vercel/speed-insights';

window.docx = docx;
window.XLSX = XLSX;

// Inject Vercel Speed Insights
injectSpeedInsights();
