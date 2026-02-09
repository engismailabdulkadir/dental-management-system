<?php

return [
    // ======================
    // Dashboard
    // ======================
    '/' => ['DashboardController', 'index'],

    // ======================
    // Authentication
    // ======================
    '/login' => ['AuthController', 'login'],
    '/login/store' => ['AuthController', 'authenticate'],
    '/logout' => ['AuthController', 'logout'],
    '/dashboard' => ['DashboardController', 'index'],
    '/doctor-dashboard' => ['DashboardController', 'doctor'],

    // ======================
    // Patients
    // ======================
    '/patients' => ['PatientController', 'index'],
    '/patients/create' => ['PatientController', 'create'],
    '/patients/store' => ['PatientController', 'store'],
    '/patients/edit'   => ['PatientController', 'edit'],
    '/patients/update' => ['PatientController', 'update'],
    '/patients/delete' => ['PatientController', 'delete'],
    '/patients/search' => ['PatientController', 'search'],

    // ======================
    // Appointments
    // ======================
    // '/appointments' => ['AppointmentController', 'index'],
    '/appointments'         => ['AppointmentController', 'index'],
    '/appointments/create'  => ['AppointmentController', 'create'],
    '/appointments/store'   => ['AppointmentController', 'store'],
    '/appointments/edit'   => ['AppointmentController', 'edit'],
    '/appointments/update' => ['AppointmentController', 'update'],
    '/appointments/delete' => ['AppointmentController', 'delete'],

    // ======================
    // doctors
    // ======================

    '/doctors'        => ['DoctorController', 'index'],
    '/doctors/create' => ['DoctorController', 'create'],
    '/doctors/store'  => ['DoctorController', 'store'],
    '/doctors/edit'   => ['DoctorController', 'edit'],
    '/doctors/update' => ['DoctorController', 'update'],
    '/doctors/delete' => ['DoctorController', 'delete'],
    '/doctors/search' => ['DoctorController', 'search'],
    // ======================
    // treatments
    // ======================
    '/treatments'        => ['TreatmentController', 'index'],
    '/treatments/create' => ['TreatmentController', 'create'],
    '/treatments/store'  => ['TreatmentController', 'store'],
    '/treatments/edit'   => ['TreatmentController', 'edit'],
    '/treatments/update' => ['TreatmentController', 'update'],
    '/treatments/delete' => ['TreatmentController', 'delete'],
    //=======================
    // produres
    //=======================
    '/procedures'        => ['ProcedureController', 'index'],
    '/procedures/create' => ['ProcedureController', 'create'],
    '/procedures/store'  => ['ProcedureController', 'store'],
    '/procedures/edit'   => ['ProcedureController', 'edit'],
    '/procedures/update' => ['ProcedureController', 'update'],
    '/procedures/delete' => ['ProcedureController', 'delete'],
    // ======================
    // treatment procedures
    // ======================
    '/treatments/procedures' => ['TreatmentProcedureController', 'create'],
    '/treatments/procedures/store' => ['TreatmentProcedureController', 'store'],
    // ======================
    // invoices
    // ======================
    '/invoices'        => ['InvoiceController', 'index'],
    '/invoices/create' => ['InvoiceController', 'create'],
    '/invoices/store'  => ['InvoiceController', 'store'],
    '/invoices/show'   => ['InvoiceController', 'show'],
    '/invoices/edit'   => ['InvoiceController', 'edit'],
    '/invoices/update' => ['InvoiceController', 'update'],
    '/invoices/delete' => ['InvoiceController', 'delete'],
    '/invoices/search' => ['InvoiceController', 'search'],
    // ======================
    // payments 
    // ======================
    '/payments'        => ['PaymentsController', 'index'],
    '/payments/create' => ['PaymentsController', 'create'],
    '/payments/edit'   => ['PaymentsController', 'edit'],
    '/payments/update' => ['PaymentsController', 'update'],
    '/payments/search' => ['PaymentsController', 'search'],
    '/payments/store'  => ['PaymentsController', 'store'],
    '/payments/delete' => ['PaymentsController', 'delete'],

    // ======================
    // allergies
    // ======================
    '/allergies'        => ['AllergyController', 'index'],
    '/allergies/create' => ['AllergyController', 'create'],
    '/allergies/store'  => ['AllergyController', 'store'],
    '/allergies/edit'   => ['AllergyController', 'edit'],
    '/allergies/update' => ['AllergyController', 'update'],
    '/allergies/delete' => ['AllergyController', 'delete'],
    '/allergies/search' => ['AllergyController', 'search'],

    // ======================
    // medical histories
    // ======================
    '/medical-histories'        => ['MedicalHistoryController', 'index'],
    '/medical-histories/create' => ['MedicalHistoryController', 'create'],
    '/medical-histories/store'  => ['MedicalHistoryController', 'store'],
    '/medical-histories/edit'   => ['MedicalHistoryController', 'edit'],
    '/medical-histories/update' => ['MedicalHistoryController', 'update'],
    '/medical-histories/delete' => ['MedicalHistoryController', 'delete'],
    '/medical-histories/search' => ['MedicalHistoryController', 'search'],

    // ======================
    // users
    // ======================
    '/users'        => ['UserController', 'index'],
    '/users/create' => ['UserController', 'create'],
    '/users/store'  => ['UserController', 'store'],
    '/users/edit'   => ['UserController', 'edit'],
    '/users/update' => ['UserController', 'update'],
    '/users/delete' => ['UserController', 'delete'],
    '/users/search' => ['UserController', 'search'],

    // ======================
    // permissions
    // ======================
    '/permissions'        => ['PermissionsController', 'index'],
    '/permissions/update' => ['PermissionsController', 'update'],
];
?>
