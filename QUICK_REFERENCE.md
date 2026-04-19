# Quick Reference: New Pages & Navigation

## 🗺️ Navigation Structure

### Main Navigation Items (Updated)

All items now accessible from the sidebar with Arabic/English labels:

| Page                | Route           | Icon                | Roles                             | Description                |
| ------------------- | --------------- | ------------------- | --------------------------------- | -------------------------- |
| Dashboard           | `/dashboard`    | squares-2x2         | All authenticated                 | Platform overview & KPIs   |
| Projects            | `/projects`     | building-office     | All authenticated                 | Construction projects list |
| **Tasks** ⭐        | `/tasks`        | check-circle        | All authenticated                 | Work items & task tracking |
| Orders              | `/orders`       | shopping-cart       | Customer, Contractor, Admin       | E-commerce orders          |
| **Transactions** ⭐ | `/transactions` | arrow-trending-up   | Customer, Contractor, Admin       | Financial transactions     |
| Reports             | `/reports`      | document-text       | Contractor, Field Engineer, Admin | Field reports              |
| Products            | `/products`     | building-storefront | Customer, Admin                   | Building materials catalog |
| Admin               | `/admin`        | cog-6-tooth         | Admin                             | Admin panel                |

**⭐** = New pages added this session

---

## 📄 New Pages Overview

### Tasks Page (`/tasks`)

**Purpose**: Browse, filter, and manage construction work items

**Features**:

- 📊 Statistics: Total tasks, completed count, in-progress count, completion %
- 🔍 Search: Find tasks by title
- 🏷️ Filtering:
  - By Status: todo, in_progress, in_review, done, blocked
  - By Priority: low, medium, high, critical
- 💳 Task Cards Display:
  - Task title and description
  - Status badge with color coding
  - Priority indicator
  - Assigned person
  - Estimated/actual hours
  - Progress indicators

**Data Source**: `GET /api/tasks?per_page=200`

**Composable**: `useTasks()`

**Demo Data**: 8 tasks with various statuses and priorities

---

### Transactions Page (`/transactions`)

**Purpose**: Track financial transactions (payments and withdrawals)

**Features**:

- 💰 Financial Summary:
  - Total transactions
  - Total payments (incoming)
  - Total withdrawals (outgoing)
  - Net balance
  - Pending count
- 🔍 Search: Find transactions by reference number or description
- 🏷️ Filtering:
  - By Type: payment, withdrawal
  - By Status: pending, completed, failed
- 💳 Transaction Cards Display:
  - Transaction type icon (+ for withdrawal, - for payment)
  - Amount in SAR
  - Status badge
  - Date created
  - Reference number
  - Description

**Data Source**: `GET /api/transactions?per_page=200`

**Composable**: `useTransactions()`

**Demo Data**: 26 transactions showing realistic payment flows

---

### Reports Page (`/reports`) - Enhanced

**Purpose**: View field progress and inspection reports

**Features**:

- 📋 Report Filtering:
  - By Type: progress, inspection
  - By Status: submitted, reviewed, approved
- 🔍 Search: Find reports by title
- 📄 Report Display:
  - Report title and content
  - Type indicator (Progress/Inspection)
  - Status badge
  - Associated task/phase/project
  - Created date and author
  - Description/content preview

**Data Source**: `GET /api/reports?per_page=200`

**Composable**: `useReports()`

**Demo Data**: 13 reports with realistic progression

---

### Dashboard Page (`/dashboard`) - Enhanced

**Purpose**: Central hub with KPIs and quick actions

**New Features**:

- 📊 Quick Actions Grid (4 cards):
  1. **Project Management** → `/projects`
  2. **Tasks** → `/tasks`
  3. **Orders** → `/orders`
  4. **Financial Transactions** → `/transactions`
- 📈 Charts:
  - Revenue trend line chart
  - Project status distribution pie chart
  - Order distribution bar chart
  - Task progress completion chart
- 📊 KPI Cards:
  - Total Projects (7)
  - Active Orders (13)
  - Total Revenue (5.2M SAR)
  - Active Users (16)
- 📋 Recent Activity: Last 8 activity items with timestamps

---

## 🧩 Composables API Reference

### `useTasks()`

```typescript
import { useTasks } from "~/composables/useTasks";

const {
  listTasks, // (params?: TaskParams) => Promise
  getTask, // (taskId: number|string) => Promise
  getProjectTasks, // (projectId: number|string, params?: TaskParams) => Promise
  createTask, // (data: TaskData) => Promise
  updateTask, // (taskId: number|string, data: TaskData) => Promise
  deleteTask, // (taskId: number|string) => Promise
} = useTasks();

// Parameters
interface TaskParams {
  per_page?: number;
  page?: number;
  status?: string; // todo|in_progress|in_review|done|blocked
  priority?: string; // low|medium|high|critical
}
```

### `useTransactions()`

```typescript
import { useTransactions } from "~/composables/useTransactions";

const {
  listTransactions, // (params?: TransactionParams) => Promise
  getTransaction, // (transactionId: number|string) => Promise
  getProjectTransactions, // (projectId: number|string, params?: TransactionParams) => Promise
} = useTransactions();

// Parameters
interface TransactionParams {
  per_page?: number;
  page?: number;
  type?: string; // payment|withdrawal
  status?: string; // pending|completed|failed
}
```

### `useReports()`

```typescript
import { useReports } from "~/composables/useReports";

const {
  listReports, // (params?: ReportParams) => Promise
  getReport, // (reportId: number|string) => Promise
  getProjectReports, // (projectId: number|string, params?: ReportParams) => Promise
  createReport, // (data: ReportData) => Promise
  updateReport, // (reportId: number|string, data: ReportData) => Promise
  deleteReport, // (reportId: number|string) => Promise
} = useReports();

// Parameters
interface ReportParams {
  per_page?: number;
  page?: number;
  type?: string; // progress|inspection
  status?: string; // submitted|reviewed|approved
}
```

---

## 🌍 i18n Support

All new pages and navigation items support both Arabic and English:

### English Translations

```json
{
  "nav": {
    "tasks": "Tasks",
    "transactions": "Transactions"
  }
}
```

### Arabic Translations

```json
{
  "nav": {
    "tasks": "المهام",
    "transactions": "العمليات المالية"
  }
}
```

### RTL Support

- Pages automatically adapt to RTL layout when language is Arabic
- Navigation items align correctly in both directions
- Form inputs and tables display properly in RTL mode

---

## 🔐 Role-Based Access Control

### Page Access Rules

| Page         | Customer | Contractor | Arch | Engineer | Admin |
| ------------ | -------- | ---------- | ---- | -------- | ----- |
| Tasks        | ✅       | ✅         | ✅   | ✅       | ✅    |
| Transactions | ✅       | ✅         | ❌   | ❌       | ✅    |
| Reports      | ❌       | ✅         | ✅   | ✅       | ✅    |
| Projects     | ✅       | ✅         | ✅   | ✅       | ✅    |
| Orders       | ✅       | ✅         | ❌   | ❌       | ✅    |
| Admin        | ❌       | ❌         | ❌   | ❌       | ✅    |

---

## 🚀 Usage Examples

### Fetch and Display Tasks

```typescript
const { listTasks } = useTasks();

const tasks = ref([]);
const loading = ref(false);

const loadTasks = async () => {
  loading.value = true;
  try {
    const response = await listTasks({
      per_page: 50,
      status: "in_progress",
      priority: "high",
    });
    tasks.value = response.data;
  } finally {
    loading.value = false;
  }
};

onMounted(() => loadTasks());
```

### Fetch Project Transactions

```typescript
const { getProjectTransactions } = useTransactions();

const projectId = route.params.id;
const transactions = await getProjectTransactions(projectId, {
  per_page: 100,
  type: "payment",
});
```

### Create New Report

```typescript
const { createReport } = useReports();

const newReport = await createReport({
  title: "Foundation Inspection Report",
  content: "All foundation work completed successfully...",
  type: "inspection",
  task_id: 5,
  phase_id: 10,
});
```

---

## 📊 Demo Data Highlights

### Task Distribution

- 2 Todo tasks
- 2 In Progress tasks
- 1 In Review task
- 2 Done tasks
- 1 Blocked task

### Transaction Breakdown

- 12 Payments (customer deposits: 100k-500k SAR)
- 14 Withdrawals (contractor earnings: 60k-900k SAR)
- 26 Total transactions with realistic amounts

### Project Stages

- 1 Draft project (Shopping Center)
- 1 Planning stage (Boutique Hotel)
- 2 In Progress projects (Villa, Residential Complex)
- 1 Completed project (Private School)
- 2 On Hold projects

---

## ✅ Testing Checklist

- [ ] Navigate to `/tasks` - verify tasks load
- [ ] Filter tasks by status - verify filtering works
- [ ] Search tasks by title - verify search works
- [ ] Navigate to `/transactions` - verify transactions load
- [ ] Check financial summary totals - verify calculations
- [ ] Filter transactions by type - verify filtering works
- [ ] Navigate to `/reports` - verify reports load
- [ ] Check dashboard quick actions - verify links work
- [ ] Test RTL mode with Arabic language
- [ ] Verify role-based access (try as different roles)
- [ ] Test pagination on each page
- [ ] Verify error handling (network errors, etc.)

---

## 📞 Support & Documentation

- **API Documentation**: `/docs/openapi/`
- **Component Library**: https://ui.nuxt.com
- **Laravel Docs**: https://laravel.com/docs
- **Vue 3 Composition API**: https://v3.vuejs.org/guide/composition-api-introduction.html

---

**Last Updated**: 2024  
**Version**: 1.0  
**Status**: ✅ Ready for Testing
