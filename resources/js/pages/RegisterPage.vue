<script setup>
import { ref } from 'vue';
import { Form } from '@primevue/forms';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import AutoComplete from 'primevue/autocomplete';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Message from 'primevue/message';
import Checkbox from 'primevue/checkbox';
import { useRouter } from 'vue-router';

const router = useRouter();

const initialValues = ref({
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    role: 'student',
    course: '',
    // Philippine address fields
    addressLine: '',
    barangay: '',
    cityMunicipality: '',
    province: '',
    region: '',
    postalCode: '',
    agreeToTerms: false
});

const resolver = ({ values }) => {
    const errors = {};

    if (!values.name || !values.name.trim()) {
        errors.name = [{ message: 'Full name is required.' }];
    }

    if (!values.email || !values.email.trim()) {
        errors.email = [{ message: 'Email is required.' }];
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) {
        errors.email = [{ message: 'Please enter a valid email address.' }];
    }

    if (!values.password) {
        errors.password = [{ message: 'Password is required.' }];
    } else if (values.password.length < 8) {
        errors.password = [{ message: 'Password must be at least 8 characters.' }];
    }

    if (!values.confirmPassword) {
        errors.confirmPassword = [{ message: 'Please confirm your password.' }];
    } else if (values.password !== values.confirmPassword) {
        errors.confirmPassword = [{ message: 'Passwords do not match.' }];
    }

    if (values.role !== 'teacher' && values.role !== 'student') {
        errors.role = [{ message: 'Please select your role.' }];
    }

    if (!values.course || !values.course.trim()) {
        errors.course = [{ message: 'Course is required.' }];
    }
    if (!values.addressLine || !values.addressLine.trim()) {
        errors.addressLine = [{ message: 'Street address is required.' }];
    }

    if (!values.barangay || !values.barangay.trim()) {
        errors.barangay = [{ message: 'Barangay is required.' }];
    }

    if (!values.cityMunicipality || !values.cityMunicipality.trim()) {
        errors.cityMunicipality = [{ message: 'City / Municipality is required.' }];
    }

    if (!values.province || !values.province.trim()) {
        errors.province = [{ message: 'Province is required.' }];
    }

    if (!values.region) {
        errors.region = [{ message: 'Region is required.' }];
    }

    if (!values.postalCode || !values.postalCode.trim()) {
        errors.postalCode = [{ message: 'Postal code is required.' }];
    } else if (!/^\d{4}$/.test(values.postalCode)) {
        errors.postalCode = [{ message: 'Enter a valid 4-digit postal code.' }];
    }

    if (!values.agreeToTerms) {
        errors.agreeToTerms = [{ message: 'You must agree to the terms and conditions.' }];
    }

    return { errors };
};

const onFormSubmit = (e) => {
    if (e.valid) {
        console.log('Registration data:', initialValues.value);
        // Handle registration logic here
    }
};

// Philippine Regions
const philippineRegions = [
    { label: 'Select Region', value: '' },
    { label: 'Region I – Ilocos Region', value: 'region-1' },
    { label: 'Region II – Cagayan Valley', value: 'region-2' },
    { label: 'Region III – Central Luzon', value: 'region-3' },
    { label: 'Region IV-A – Calabarzon', value: 'region-4a' },
    { label: 'Region IV-B – Mimaropa', value: 'region-4b' },
    { label: 'Region V – Bicol Region', value: 'region-5' },
    { label: 'Region VI – Western Visayas', value: 'region-6' },
    { label: 'Region VII – Central Visayas', value: 'region-7' },
    { label: 'Region VIII – Eastern Visayas', value: 'region-8' },
    { label: 'Region IX – Zamboanga Peninsula', value: 'region-9' },
    { label: 'Region X – Northern Mindanao', value: 'region-10' },
    { label: 'Region XI – Davao Region', value: 'region-11' },
    { label: 'Region XII – Soccsksargen', value: 'region-12' },
    { label: 'Region XIII – Caraga', value: 'region-13' },
    { label: 'BAC – Bangsamoro Autonomous Region', value: 'region-bar' },
    { label: 'NCR – National Capital Region', value: 'region-ncr' },
    { label: 'CAR – Cordillera Administrative Region', value: 'region-car' },
];

const roleOptions = [
    { label: 'Teacher', value: 'teacher' },
    { label: 'Student', value: 'student' },
];

const philippineCourses = [
    { label: 'Select Course', value: '' },
    { label: 'BS Information Technology', value: 'bsit' },
    { label: 'BS Computer Science', value: 'bscs' },
    { label: 'BS Information Systems', value: 'bsis' },
    { label: 'BS Software Engineering', value: 'bsse' },
    { label: 'BS Data Science', value: 'bsds' },
    { label: 'BS Cybersecurity', value: 'bscscy' },
    { label: 'BS Business Administration', value: 'bsba' },
    { label: 'BS Accountancy', value: 'bsa' },
    { label: 'BS Marketing', value: 'bsmkt' },
    { label: 'BS Economics', value: 'bse' },
    { label: 'BS Psychology', value: 'bsp' },
    { label: 'BS Nursing', value: 'bsn' },
    { label: 'BS Education', value: 'bsed' },
    { label: 'BS Engineering', value: 'bse' },
    { label: 'BA Communication', value: 'bacomm' },
    { label: 'BA Political Science', value: 'baps' },
    { label: 'BS Hospitality Management', value: 'bshm' },
    { label: 'BS Tourism Management', value: 'bstm' },
    { label: 'BS Architecture', value: 'bsa' },
    { label: 'BS Civil Engineering', value: 'bsce' },
    { label: 'BS Electrical Engineering', value: 'bsee' },
    { label: 'BS Mechanical Engineering', value: 'bsme' },
    { label: 'BS Electronics Engineering', value: 'bsece' },
    { label: 'BS Accountancy', value: 'bsa' },
    { label: 'BS Entrepreneurship', value: 'bse' },
    { label: 'Master of Business Administration', value: 'mba' },
    { label: 'Master of Education', value: 'med' },
    { label: 'Doctor of Medicine', value: 'md' },
    { label: 'LLB – Bachelor of Laws', value: 'llb' },
    { label: 'Other', value: 'other' },
];

const philippineCitiesByRegion = {
    'region-1': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Laoag City', value: 'laoag' },
        { label: 'Vigan City', value: 'vigan' },
        { label: 'San Fernando City', value: 'san-fernando-la-union' },
        { label: 'Alaminos', value: 'alaminos' },
        { label: 'Dagupan City', value: 'dagupan' },
        { label: 'San Carlos City', value: 'san-carlos-pangasinan' },
        { label: 'Urdaneta City', value: 'urdaneta' },
        { label: 'Candon City', value: 'candon' },
    ],
    'region-2': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Tuguegarao City', value: 'tuguegarao' },
        { label: 'Cauayan City', value: 'cauayan' },
        { label: 'Ilagan City', value: 'ilagan' },
        { label: 'Santiago City', value: 'santiago' },
        { label: 'Bayombong', value: 'bayombong' },
        { label: 'Laoangan', value: 'lallo' },
    ],
    'region-3': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'San Fernando (Pampanga)', value: 'san-fernando-pampanga' },
        { label: 'Angeles City', value: 'angeles' },
        { label: 'Mabalacat City', value: 'mabalacat' },
        { label: 'Olongapo City', value: 'olongapo' },
        { label: 'Tarlac City', value: 'tarlac-city' },
        { label: 'Cabanatuan City', value: 'cabanatuan' },
        { label: 'Palayan City', value: 'palayan' },
        { label: 'Gapan City', value: 'gapan' },
        { label: 'Malolos City', value: 'malolos' },
        { label: 'Meycauayan City', value: 'meycauayan' },
        { label: 'San Jose del Monte', value: 'san-jose-del-monte' },
        { label: 'Baler', value: 'baler' },
        { label: 'Olongapo', value: 'olongapo' },
    ],
    'region-4a': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Quezon City', value: 'quezon-city' },
        { label: 'Manila', value: 'manila' },
        { label: 'Caloocan', value: 'caloocan' },
        { label: 'Pasig', value: 'pasig' },
        { label: 'Taguig', value: 'taguig' },
        { label: 'Makati', value: 'makati' },
        { label: 'Pasay', value: 'pasay' },
        { label: 'Parañaque', value: 'paranaque' },
        { label: 'Las Piñas', value: 'las-pinas' },
        { label: 'Muntinlupa', value: 'muntinlupa' },
        { label: 'Mandaluyong', value: 'mandaluyong' },
        { label: 'San Juan', value: 'san-juan-ncr' },
        { label: 'Navotas', value: 'navotas' },
        { label: 'Malabon', value: 'malabon' },
        { label: 'Valenzuela', value: 'valenzuela' },
        { label: 'Marikina', value: 'marikina' },
        { label: 'Batangas City', value: 'batangas-city' },
        { label: 'Lipa City', value: 'lipa' },
        { label: 'Tanauan City', value: 'tanauan' },
        { label: 'Calamba City', value: 'calamba' },
        { label: 'Santa Rosa City', value: 'santa-rosa' },
        { label: 'Bacoor', value: 'bacoor' },
        { label: 'Imus', value: 'imus' },
        { label: 'Dasmariñas', value: 'dasmarinas' },
        { label: 'Tagaytay', value: 'tagaytay' },
        { label: 'Lucena City', value: 'lucena' },
        { label: 'Antipolo', value: 'antipolo' },
        { label: 'Cainta', value: 'cainta' },
        { label: 'Biñan', value: 'binan' },
        { label: 'Cavite City', value: 'cavite-city' },
        { label: 'General Trias', value: 'general-trias' },
    ],
    'region-4b': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Calapan City', value: 'calapan' },
        { label: 'Puerto Princesa City', value: 'puerto-princesa' },
        { label: 'Romblon', value: 'romblon' },
    ],
    'region-5': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Legazpi City', value: 'legazpi' },
        { label: 'Naga City', value: 'naga-city' },
        { label: 'Ligao City', value: 'ligao' },
        { label: 'Tabaco City', value: 'tabaco' },
        { label: 'Masbate City', value: 'masbate-city' },
        { label: 'Sorsogon City', value: 'sorsogon-city' },
        { label: 'Daraga', value: 'daraga' },
    ],
    'region-6': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Iloilo City', value: 'iloilo-city' },
        { label: 'Bacolod City', value: 'bacolod' },
        { label: 'Roxas City', value: 'roxas' },
        { label: 'Kalibo', value: 'kalibo' },
        { label: 'Eskay', value: 'eskay' },
        { label: 'San Carlos City (Negros)', value: 'san-carlos-negros' },
        { label: 'Silay City', value: 'silay' },
        { label: 'Talisay City', value: 'talisay-negros' },
        { label: 'Bago City', value: 'bago' },
        { label: 'Kabankalan City', value: 'kabankalan' },
        { label: 'Cadiz', value: 'cadiz' },
    ],
    'region-7': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Cebu City', value: 'cebu-city' },
        { label: 'Mandaue City', value: 'mandaue' },
        { label: 'Lapu-Lapu City', value: 'lapu-lapu' },
        { label: 'Talisay City (Cebu)', value: 'talisay-cebu' },
        { label: 'Toledo City', value: 'toledo-cebu' },
        { label: 'Danao City', value: 'danao' },
        { label: 'Tagbilaran City', value: 'tagbilaran' },
        { label: 'Bogo City', value: 'bogo' },
        { label: 'Naga City (Cebu)', value: 'naga-cebu' },
    ],
    'region-8': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Tacloban City', value: 'tacloban' },
        { label: 'Ormoc City', value: 'ormoc' },
        { label: 'Calbayog City', value: 'calbayog' },
        { label: 'Maasin City', value: 'maasin' },
        { label: 'Catbalogan', value: 'catbalogan' },
        { label: 'Borongan', value: 'borongan' },
        { label: 'Basey', value: 'basey' },
    ],
    'region-9': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Zamboanga City', value: 'zamboanga-city' },
        { label: 'Dipolog City', value: 'dipolog' },
        { label: 'Pagadian City', value: 'pagadian' },
        { label: 'Isabela City', value: 'isabela-zamb' },
    ],
    'region-10': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Cagayan de Oro City', value: 'cdo' },
        { label: 'Iligan City', value: 'iligan' },
        { label: 'Oroquieta', value: 'oroquieta' },
        { label: 'Ozamiz', value: 'ozamiz' },
        { label: 'Tangub', value: 'tangub' },
        { label: 'El Salvador', value: 'el-salvador' },
        { label: 'Gingoog City', value: 'gingoog' },
        { label: 'Malaybalay City', value: 'malaybalay' },
        { label: 'Valencia City (Bukidnon)', value: 'valencia-bukidnon' },
    ],
    'region-11': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Davao City', value: 'davao-city' },
        { label: 'Tagum City', value: 'tagum' },
        { label: 'Digos City', value: 'digos' },
        { label: 'Panabo City', value: 'panabo' },
        { label: 'Mati City', value: 'mati' },
        { label: 'Samal Island', value: 'samal' },
    ],
    'region-12': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Koronadal City', value: 'koronadal' },
        { label: 'General Santos City', value: 'general-santos' },
        { label: 'Kidapawan City', value: 'kidapawan' },
        { label: 'Tacurong City', value: 'tacurong' },
        { label: 'Bayambang', value: 'bayambang' },
        { label: 'Sultan Kudarat', value: 'sultan-kudarat-town' },
    ],
    'region-13': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Butuan City', value: 'butuan' },
        { label: 'Cabadbaran', value: 'cabadbaran' },
        { label: 'Bayugan', value: 'bayugan' },
        { label: 'Surigao City', value: 'surigao-city' },
        { label: 'Tandag', value: 'tandag' },
        { label: 'Bislig', value: 'bislig' },
    ],
    'region-bar': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Cotabato City', value: 'cotabato-city' },
        { label: 'Marawi City', value: 'marawi' },
        { label: 'Lamitan City', value: 'lamitan' },
        { label: 'Isulan', value: 'isulan' },
        { label: 'Buluan', value: 'buluan' },
    ],
    'region-ncr': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Manila', value: 'manila' },
        { label: 'Quezon City', value: 'quezon-city' },
        { label: 'Caloocan', value: 'caloocan' },
        { label: 'Pasig', value: 'pasig' },
        { label: 'Taguig', value: 'taguig' },
        { label: 'Makati', value: 'makati' },
        { label: 'Pasay', value: 'pasay' },
        { label: 'Parañaque', value: 'paranaque' },
        { label: 'Las Piñas', value: 'las-pinas' },
        { label: 'Muntinlupa', value: 'muntinlupa' },
        { label: 'Mandaluyong', value: 'mandaluyong' },
        { label: 'San Juan', value: 'san-juan-ncr' },
        { label: 'Navotas', value: 'navotas' },
        { label: 'Malabon', value: 'malabon' },
        { label: 'Valenzuela', value: 'valenzuela' },
        { label: 'Marikina', value: 'marikina' },
    ],
    'region-car': [
        { label: 'Select City / Municipality', value: '' },
        { label: 'Baguio City', value: 'baguio' },
        { label: 'Tabuk City', value: 'tabuk' },
        { label: 'La Trinidad', value: 'la-trinidad' },
        { label: 'Bangued', value: 'bangued' },
        { label: 'Kabugao', value: 'kabugao' },
    ],
};

const philippineBarangaysByCity = {
    'manila': [
        { label: 'Select Barangay', value: '' },
        { label: 'Barangay 1 (San Nicolas)', value: 'brgy-1' },
        { label: 'Barangay 2 (San Nicolas)', value: 'brgy-2' },
        { label: 'Barangay 3 (San Nicolas)', value: 'brgy-3' },
        { label: 'Barangay 4 (San Nicolas)', value: 'brgy-4' },
        { label: 'Barangay 5 (San Nicolas)', value: 'brgy-5' },
        { label: 'Barangay 6 (San Nicolas)', value: 'brgy-6' },
        { label: 'Barangay 7 (Binondo)', value: 'brgy-7' },
        { label: 'Barangay 8 (Binondo)', value: 'brgy-8' },
        { label: 'Barangay 9 (Binondo)', value: 'brgy-9' },
        { label: 'Barangay 10 (Binondo)', value: 'brgy-10' },
        { label: 'Barangay 11 (Santa Cruz)', value: 'brgy-11' },
        { label: 'Barangay 12 (Santa Cruz)', value: 'brgy-12' },
        { label: 'Barangay 13 (Santa Cruz)', value: 'brgy-13' },
        { label: 'Barangay 14 (Santa Cruz)', value: 'brgy-14' },
        { label: 'Barangay 15 (Santa Cruz)', value: 'brgy-15' },
        { label: 'Barangay 16 (Santa Cruz)', value: 'brgy-16' },
        { label: 'Barangay 17 (Sampaloc)', value: 'brgy-17' },
        { label: 'Barangay 18 (Sampaloc)', value: 'brgy-18' },
        { label: 'Barangay 19 (Sampaloc)', value: 'brgy-19' },
        { label: 'Barangay 20 (Sampaloc)', value: 'brgy-20' },
        { label: 'Barangay 21 (Sampaloc)', value: 'brgy-21' },
        { label: 'Barangay 22 (Sampaloc)', value: 'brgy-22' },
        { label: 'Barangay 23 (Sampaloc)', value: 'brgy-23' },
        { label: 'Barangay 24 (Sampaloc)', value: 'brgy-24' },
        { label: 'Barangay 25 (Sampaloc)', value: 'brgy-25' },
        { label: 'Barangay 26 (Sampaloc)', value: 'brgy-26' },
        { label: 'Barangay 27 (Sampaloc)', value: 'brgy-27' },
        { label: 'Barangay 28 (Sampaloc)', value: 'brgy-28' },
        { label: 'Barangay 29 (Sampaloc)', value: 'brgy-29' },
        { label: 'Barangay 30 (Sampaloc)', value: 'brgy-30' },
        { label: 'Barangay 31 (Ermita)', value: 'brgy-31' },
        { label: 'Barangay 32 (Ermita)', value: 'brgy-32' },
        { label: 'Barangay 33 (Ermita)', value: 'brgy-33' },
        { label: 'Barangay 34 (Ermita)', value: 'brgy-34' },
        { label: 'Barangay 35 (Ermita)', value: 'brgy-35' },
        { label: 'Barangay 36 (Ermita)', value: 'brgy-36' },
        { label: 'Barangay 37 (Malate)', value: 'brgy-37' },
        { label: 'Barangay 38 (Malate)', value: 'brgy-38' },
        { label: 'Barangay 39 (Malate)', value: 'brgy-39' },
        { label: 'Barangay 40 (Malate)', value: 'brgy-40' },
        { label: 'Barangay 41 (Malate)', value: 'brgy-41' },
        { label: 'Barangay 42 (Malate)', value: 'brgy-42' },
        { label: 'Barangay 43 (Paco)', value: 'brgy-43' },
        { label: 'Barangay 44 (Paco)', value: 'brgy-44' },
        { label: 'Barangay 45 (Paco)', value: 'brgy-45' },
        { label: 'Barangay 46 (Paco)', value: 'brgy-46' },
        { label: 'Barangay 47 (Paco)', value: 'brgy-47' },
        { label: 'Barangay 48 (Paco)', value: 'brgy-48' },
        { label: 'Barangay 49 (Paco)', value: 'brgy-49' },
        { label: 'Barangay 50 (Paco)', value: 'brgy-50' },
        { label: 'Barangay 51 (Paco)', value: 'brgy-51' },
        { label: 'Barangay 52 (Paco)', value: 'brgy-52' },
        { label: 'Barangay 53 (Paco)', value: 'brgy-53' },
        { label: 'Barangay 54 (Paco)', value: 'brgy-54' },
        { label: 'Barangay 55 (Paco)', value: 'brgy-55' },
        { label: 'Barangay 56 (Paco)', value: 'brgy-56' },
        { label: 'Barangay 57 (Paco)', value: 'brgy-57' },
        { label: 'Barangay 58 (Paco)', value: 'brgy-58' },
        { label: 'Barangay 59 (Paco)', value: 'brgy-59' },
        { label: 'Barangay 60 (Paco)', value: 'brgy-60' },
        { label: 'Barangay 61 (Paco)', value: 'brgy-61' },
        { label: 'Barangay 62 (Paco)', value: 'brgy-62' },
        { label: 'Barangay 63 (Paco)', value: 'brgy-63' },
        { label: 'Barangay 64 (Paco)', value: 'brgy-64' },
        { label: 'Barangay 65 (Paco)', value: 'brgy-65' },
        { label: 'Barangay 66 (Paco)', value: 'brgy-66' },
        { label: 'Barangay 67 (Paco)', value: 'brgy-67' },
        { label: 'Barangay 68 (Paco)', value: 'brgy-68' },
        { label: 'Barangay 69 (Paco)', value: 'brgy-69' },
        { label: 'Barangay 70 (Paco)', value: 'brgy-70' },
        { label: 'Barangay 71 (Paco)', value: 'brgy-71' },
        { label: 'Barangay 72 (Paco)', value: 'brgy-72' },
        { label: 'Barangay 73 (Paco)', value: 'brgy-73' },
        { label: 'Barangay 74 (Paco)', value: 'brgy-74' },
        { label: 'Barangay 75 (Paco)', value: 'brgy-75' },
        { label: 'Barangay 76 (Paco)', value: 'brgy-76' },
        { label: 'Barangay 77 (Paco)', value: 'brgy-77' },
        { label: 'Barangay 78 (Paco)', value: 'brgy-78' },
        { label: 'Barangay 79 (Paco)', value: 'brgy-79' },
        { label: 'Barangay 80 (Paco)', value: 'brgy-80' },
        { label: 'Barangay 81 (Paco)', value: 'brgy-81' },
        { label: 'Barangay 82 (Paco)', value: 'brgy-82' },
        { label: 'Barangay 83 (Paco)', value: 'brgy-83' },
        { label: 'Barangay 84 (Paco)', value: 'brgy-84' },
        { label: 'Barangay 85 (Paco)', value: 'brgy-85' },
        { label: 'Barangay 86 (Paco)', value: 'brgy-86' },
        { label: 'Barangay 87 (Paco)', value: 'brgy-87' },
        { label: 'Barangay 88 (Paco)', value: 'brgy-88' },
        { label: 'Barangay 89 (Paco)', value: 'brgy-89' },
        { label: 'Barangay 90 (Paco)', value: 'brgy-90' },
        { label: 'Barangay 91 (Paco)', value: 'brgy-91' },
        { label: 'Barangay 92 (Paco)', value: 'brgy-92' },
        { label: 'Barangay 93 (Paco)', value: 'brgy-93' },
        { label: 'Barangay 94 (Paco)', value: 'brgy-94' },
        { label: 'Barangay 95 (Paco)', value: 'brgy-95' },
        { label: 'Barangay 96 (Paco)', value: 'brgy-96' },
        { label: 'Barangay 97 (Paco)', value: 'brgy-97' },
        { label: 'Barangay 98 (Paco)', value: 'brgy-98' },
        { label: 'Barangay 99 (Paco)', value: 'brgy-99' },
        { label: 'Barangay 100 (Paco)', value: 'brgy-100' },
    ],
    'quezon-city': [
        { label: 'Select Barangay', value: '' },
        { label: 'Bagong Pag-asa', value: 'bp' },
        { label: 'Bahay Toro', value: 'bht' },
        { label: 'Banawe', value: 'banawe' },
        { label: 'Bgys. Paang Bundok', value: 'bp-bundok' },
        { label: 'Bagong Silangan', value: 'bagong-silangan' },
        { label: 'Batasan Hills', value: 'batasan' },
        { label: 'Commonwealth', value: 'commonwealth' },
        { label: 'Culiat', value: 'culiat' },
        { label: 'East Kamias', value: 'east-kamias' },
        { label: 'Fairview', value: 'fairview' },
        { label: 'Greenhills', value: 'greenhills' },
        { label: 'Immaculate Concepcion', value: 'ic' },
        { label: 'Kamias', value: 'kamias' },
        { label: 'Katipunan', value: 'katipunan' },
        { label: 'Krus na Ligas', value: 'knl' },
        { label: 'La Loma', value: 'la-loma' },
        { label: 'Laging Handa', value: 'lh' },
        { label: 'Matalino', value: 'matalino' },
        { label: 'New Era', value: 'new-era' },
        { label: 'Novaliches', value: 'novaliches' },
        { label: 'Old Capitol', value: 'old-capitol' },
        { label: 'Pansol', value: 'pansol-qc' },
        { label: 'Pinyahan', value: 'pinyahan' },
        { label: 'Project 4', value: 'proj-4' },
        { label: 'Project 6', value: 'proj-6' },
        { label: 'Quirino District', value: 'quirino' },
        { label: 'Roxas', value: 'roxas' },
        { label: 'San Bartolome', value: 'sb' },
        { label: 'San Isidro', value: 'si' },
        { label: 'San Jose', value: 'sj' },
        { label: 'Santa Lucia', value: 'sl' },
        { label: 'Santo Cristo', value: 'sc' },
        { label: 'Socorro', value: 'socorro' },
        { label: 'South Triangle', value: 'st' },
        { label: 'Tandang Sora', value: 'ts' },
        { label: 'Teachers Village', value: 'tv' },
        { label: 'U.P. Campus', value: 'up-campus' },
        { label: 'UP East', value: 'up-east' },
        { label: 'Valencia', value: 'valencia' },
        { label: 'Veterans Village', value: 'vv' },
        { label: 'West Kamias', value: 'wk' },
    ],
    'cebu-city': [
        { label: 'Select Barangay', value: '' },
        { label: 'Adlaon', value: 'adlaon' },
        { label: 'Agsungot', value: 'agsungot' },
        { label: 'Apas', value: 'apas' },
        { label: 'Babag', value: 'babag' },
        { label: 'Bacayan', value: 'bacayan' },
        { label: 'Banilad', value: 'banilad' },
        { label: 'Basak San Nicolas', value: 'basak-sn' },
        { label: 'Basak Pardo', value: 'basak-pardo' },
        { label: 'Binaliw', value: 'binaliw' },
        { label: 'Bolinao', value: 'bolinao' },
        { label: 'Bonbon', value: 'bonbon' },
        { label: 'Buot', value: 'buot' },
        { label: 'Busay', value: 'busay' },
        { label: 'Calamba', value: 'calamba-cebu' },
        { label: 'Cambinocot', value: 'cambinocot' },
        { label: 'Carreta', value: 'carreta' },
        { label: 'Cogon Ramos', value: 'cogon-ramos' },
        { label: 'Day-as', value: 'dayas' },
        { label: 'Duljo', value: 'duljo' },
        { label: 'Ermita', value: 'ermita' },
        { label: 'Guadalupe', value: 'guadalupe' },
        { label: 'Guba', value: 'guba' },
        { label: 'Hipodromo', value: 'hipodromo' },
        { label: 'Inayawan', value: 'inayawan' },
        { label: 'Kalunasan', value: 'kalunasan' },
        { label: 'Kamagayan', value: 'kamagayan' },
        { label: 'Kasambagan', value: 'kasambagan' },
        { label: 'Kinas-a-on', value: 'kinas-a-on' },
        { label: 'Labangon', value: 'labangon' },
        { label: 'Lahug', value: 'lahug' },
        { label: 'Lorega', value: 'lorega' },
        { label: 'Lusaran', value: 'lusaran' },
        { label: 'Mabini', value: 'mabini-cebu' },
        { label: 'Mabolo', value: 'mabolo' },
        { label: 'Malubog', value: 'malubog' },
        { label: 'Mambaling', value: 'mambaling' },
        { label: 'Pahina San Nicolas', value: 'psn' },
        { label: 'Pamutan', value: 'pamutan' },
        { label: 'Pardo', value: 'pardo' },
        { label: 'Paril', value: 'paril' },
        { label: 'Pasil', value: 'pasil' },
        { label: 'Pit-os', value: 'pitos' },
        { label: 'Pulangbato', value: 'pulangbato' },
        { label: 'Pung-ol Sibugay', value: 'pung-ol' },
        { label: 'Sambag I', value: 'sambag-1' },
        { label: 'Sambag II', value: 'sambag-2' },
        { label: 'San Antonio', value: 'san-antonio-cebu' },
        { label: 'San Fernando', value: 'san-fernando-cebu' },
        { label: 'San Jose', value: 'san-jose-cebu' },
        { label: 'San Nicolas Proper', value: 'sn-proper' },
        { label: 'Santa Cruz', value: 'santa-cruz-cebu' },
        { label: 'Santo Niño', value: 'santo-nino' },
        { label: 'Sapangdaku', value: 'sapangdaku' },
        { label: 'Sawang Calero', value: 'sawang' },
        { label: 'Sinsin', value: 'sinsin' },
        { label: 'Suba', value: 'suba' },
        { label: 'Sudlon I', value: 'sudlon-1' },
        { label: 'Sudlon II', value: 'sudlon-2' },
        { label: 'Tabunan', value: 'tabunan' },
        { label: 'Tagba-o', value: 'tagbao' },
        { label: 'Talamban', value: 'talamban' },
        { label: 'T. Padilla', value: 't-padilla' },
        { label: 'Tisa', value: 'tisa' },
        { label: 'To-ong', value: 'toong' },
        { label: 'T. Herrera', value: 't-herrera' },
        { label: 'Urijoe', value: 'urijoe' },
    ],
    'baguio': [
        { label: 'Select Barangay', value: '' },
        { label: 'Andrei F. Bautista', value: 'af-bautista' },
        { label: 'A. Bonifacio', value: 'a-bonifacio' },
        { label: 'Ambiong', value: 'ambiong' },
        { label: 'Apugan', value: 'apugan' },
        { label: 'Bakakeng Norte', value: 'bakakeng-n' },
        { label: 'Bakakeng Sur', value: 'bakakeng-s' },
        { label: 'Balili', value: 'balili' },
        { label: 'Bayan Park', value: 'bayan-park' },
        { label: 'Brookside', value: 'brookside' },
        { label: 'Cabinet Hill-Teacher\'s Village', value: 'cabinet-hill' },
        { label: 'Camp Allen', value: 'camp-allen' },
        { label: 'Camp John Hay', value: 'camp-jh' },
        { label: 'City Camp', value: 'city-camp' },
        { label: 'Country Club Village', value: 'ccv' },
        { label: 'Dagsian', value: 'dagsian' },
        { label: 'Dil-ot (Pinsao)', value: 'dil-ot' },
        { label: 'Engineers\' Hill', value: 'engineers-hill' },
        { label: 'Fort del Pilar', value: 'fdp' },
        { label: 'Friska', value: 'friska' },
        { label: 'General Luna', value: 'gen-luna' },
        { label: 'General Villar', value: 'gen-villar' },
        { label: 'Gibraltar', value: 'gibraltar' },
        { label: 'Happy Hollow', value: 'happy-hollow' },
        { label: 'Hillside', value: 'hillside' },
        { label: 'Horizons', value: 'horizons' },
        { label: 'Imelda Park', value: 'imelda' },
        { label: 'Kayang', value: 'kayang' },
        { label: 'Kias', value: 'kias' },
        { label: 'Legarda Road', value: 'legarda' },
        { label: 'Lourdes Subdivision', value: 'lourdes' },
        { label: 'Lower QM', value: 'l-qm' },
        { label: 'Malcolm Park', value: 'malcolm' },
        { label: 'Marcoville', value: 'marcoville' },
        { label: 'Magsaysay', value: 'magsaysay-baguio' },
        { label: 'Manuel A. Roxas', value: 'roxas-baguio' },
        { label: 'Market Area', value: 'market' },
        { label: 'Middle QM', value: 'm-qm' },
        { label: 'Mines View Park', value: 'mines-view' },
        { label: 'MODES', value: 'modes' },
        { label: 'New Lucban', value: 'new-lucban' },
        { label: 'O\'Malley', value: 'omalley' },
        { label: 'Padre Burgos', value: 'p-burgos' },
        { label: 'Palma', value: 'palma' },
        { label: 'Pinsao', value: 'pinsao' },
        { label: 'Pinsao Proper', value: 'pinsao-proper' },
        { label: 'Poblacion', value: 'poblacion-baguio' },
        { label: 'Project 2', value: 'proj-2' },
        { label: 'Project 3', value: 'proj-3' },
        { label: 'Project 4', value: 'proj-4-baguio' },
        { label: 'Project 5', value: 'proj-5' },
        { label: 'Project 6', value: 'proj-6-baguio' },
        { label: 'Quezon Hill', value: 'q-hill' },
        { label: 'Quirino', value: 'quirino-baguio' },
        { label: 'Rock Quarry', value: 'rq' },
        { label: 'Sagmin', value: 'sagmin' },
        { label: 'San Antonio', value: 'san-antonio-baguio' },
        { label: 'Santo Rosario', value: 'sto-rosario' },
        { label: 'Santa Escolástica', value: 'sta-escolastica' },
        { label: 'Santo Niño', value: 'sto-nino-baguio' },
        { label: 'Saint Joseph Village', value: 'sjv' },
        { label: 'Sessions Road', value: 'sessions' },
        { label: 'Solcom', value: 'solcom' },
        { label: 'South Drive', value: 'south-drive' },
        { label: 'Trancoville', value: 'trancoville' },
        { label: 'Upper QM', value: 'u-qm' },
        { label: 'Valencia', value: 'valencia-baguio' },
    ],
    'davao-city': [
        { label: 'Select Barangay', value: '' },
        { label: 'Agdao', value: 'agdao' },
        { label: 'Buhangin', value: 'buhangin' },
        { label: 'Bunawan', value: 'bunawan' },
        { label: 'Calinan', value: 'calinan' },
        { label: 'Davao City Proper', value: 'dcp' },
        { label: 'Digos', value: 'digos-dvo' },
        { label: 'Dumoy', value: 'dumoy' },
        { label: 'Guiling', value: 'guiling' },
        { label: 'Langub', value: 'langub' },
        { label: 'Mabini', value: 'mabini-dvo' },
        { label: 'Marilog', value: 'marilog' },
        { label: 'Matina Aplaya', value: 'matina-a' },
        { label: 'Matina Crossing', value: 'matina-c' },
        { label: 'Matina Pangi', value: 'matina-p' },
        { label: 'Mintal', value: 'mintal' },
        { label: 'Paquibato', value: 'paquibato' },
        { label: 'Rivera', value: 'rivera' },
        { label: 'Sasa', value: 'sasa' },
        { label: 'Tibungco', value: 'tibungco' },
        { label: 'Toril', value: 'toril' },
        { label: 'Tugbok', value: 'tugbok' },
        { label: 'Ula', value: 'ula' },
    ],
    'cebu-city': [
        { label: 'Select Barangay', value: '' },
        { label: 'Adlaon', value: 'adlaon' },
        { label: 'Agsungot', value: 'agsungot' },
        { label: 'Apas', value: 'apas' },
        { label: 'Babag', value: 'babag' },
        { label: 'Bacayan', value: 'bacayan' },
        { label: 'Banilad', value: 'banilad' },
        { label: 'Basak', value: 'basak' },
        { label: 'Binaliw', value: 'binaliw' },
        { label: 'Bonbon', value: 'bonbon' },
        { label: 'Buot', value: 'buot' },
        { label: 'Busay', value: 'busay' },
        { label: 'Carreta', value: 'carreta' },
        { label: 'Cogon Ramos', value: 'cogon-ramos' },
        { label: 'Day-as', value: 'day-as' },
        { label: 'Ermita', value: 'ermita-cebu' },
        { label: 'Guadalupe', value: 'guadalupe-cebu' },
        { label: 'Guba', value: 'guba' },
        { label: 'Hipodromo', value: 'hipodromo' },
        { label: 'Inayawan', value: 'inayawan' },
        { label: 'Kalunasan', value: 'kalunasan' },
        { label: 'Kamagayan', value: 'kamagayan' },
        { label: 'Kasambagan', value: 'kasambagan' },
        { label: 'Labangon', value: 'labangon' },
        { label: 'Lahug', value: 'lahug' },
        { label: 'Lorega', value: 'lorega' },
        { label: 'Mabini', value: 'mabini-cebu' },
        { label: 'Mabolo', value: 'mabolo' },
        { label: 'Mambaling', value: 'mambaling' },
        { label: 'Pardo', value: 'pardo-cebu' },
        { label: 'Pasil', value: 'pasil-cebu' },
        { label: 'Pit-os', value: 'pitos' },
        { label: 'Sambag I', value: 'sambag-1' },
        { label: 'Sambag II', value: 'sambag-2' },
        { label: 'San Antonio', value: 'san-antonio-cebu' },
        { label: 'San Jose', value: 'san-jose-cebu' },
        { label: 'Santa Cruz', value: 'santa-cruz-cebu' },
        { label: 'Santo Niño', value: 'santo-nino-cebu' },
        { label: 'Suba', value: 'suba-cebu' },
        { label: 'Talamban', value: 'talamban' },
        { label: 'T. Padilla', value: 't-padilla' },
        { label: 'Tisa', value: 'tisa' },
    ],
};

function citiesForRegion(regionValue) {
    if (!regionValue) {
        return [{ label: 'Select a Region first', value: '' }];
    }
    return philippineCitiesByRegion[regionValue] || [
        { label: 'No cities available', value: '' },
    ];
}

function barangaysForCity(cityValue) {
    if (!cityValue) {
        return [{ label: 'Select a City first', value: '' }];
    }
    return philippineBarangaysByCity[cityValue] || [
        { label: 'No barangays available', value: '' },
    ];
}
</script>

<template>
    <div class="register-form-wrapper">
        <!-- Back to Homepage -->
        <button class="back-button" @click="router.push('/')">
            <i class="pi pi-arrow-left"></i>
            Back to Homepage
        </button>

        <div class="form-header">
            <h2 class="form-title">Create your account</h2>
            <p class="form-subtitle">Get started with your free account today.</p>
        </div>

        <Form
            v-slot="$form"
            :resolver="resolver"
            :initialValues="initialValues"
            @submit="onFormSubmit"
            class="register-form"
        >
            <!-- Role Selector -->
            <div class="form-field">
                <label class="form-label">I am a</label>
                <div class="role-selector">
                    <label 
                        class="role-option" 
                        :class="{ active: initialValues.role === 'teacher' }"
                    >
                        <input
                            type="radio"
                            name="role"
                            value="teacher"
                            v-model="initialValues.role"
                            class="hidden-radio"
                        />
                        <div class="role-content">
                            <i class="pi pi-chalkboard-teacher"></i>
                            <span>Teacher</span>
                        </div>
                    </label>
                    <label 
                        class="role-option" 
                        :class="{ active: initialValues.role === 'student' }"
                    >
                        <input
                            type="radio"
                            name="role"
                            value="student"
                            v-model="initialValues.role"
                            class="hidden-radio"
                        />
                        <div class="role-content">
                            <i class="pi pi-user-graduate"></i>
                            <span>Student</span>
                        </div>
                    </label>
                </div>
                <Message
                    v-if="$form.role?.invalid"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ $form.role.error?.message }}
                </Message>
            </div>

            <!-- Account Fields -->
            <div class="form-row">
                <div class="form-field">
                    <label for="name" class="form-label">Full Name</label>
                    <InputText
                        id="name"
                        name="name"
                        type="text"
                        placeholder="Enter your full name"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.name?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.name.error?.message }}
                    </Message>
                </div>

                <div class="form-field">
                    <label for="email" class="form-label">Email Address</label>
                    <InputText
                        id="email"
                        name="email"
                        type="email"
                        placeholder="Enter your email"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.email?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.email.error?.message }}
                    </Message>
                </div>
            </div>

            <!-- Password Fields -->
            <div class="form-row">
                <div class="form-field">
                    <label for="password" class="form-label">Password</label>
                    <Password
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Create a password"
                        :feedback="true"
                        :weakLabel="'Weak'"
                        :mediumLabel="'Medium'"
                        :strongLabel="'Strong'"
                        class="form-input"
                        inputClass="w-full"
                    />
                    <Message
                        v-if="$form.password?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.password.error?.message }}
                    </Message>
                </div>

                <div class="form-field">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <Password
                        id="confirmPassword"
                        name="confirmPassword"
                        type="password"
                        placeholder="Confirm your password"
                        :feedback="false"
                        class="form-input"
                        inputClass="w-full"
                    />
                    <Message
                        v-if="$form.confirmPassword?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.confirmPassword.error?.message }}
                    </Message>
                </div>
            </div>

            <div class="form-section-divider">
                <span>Course Information</span>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="course" class="form-label">Course</label>
                    <Select
                        v-model="initialValues.course"
                        :options="philippineCourses"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select your course"
                        class="form-select"
                        :highlightOnSelect="false"
                        showClear
                        filter
                    />
                    <Message
                        v-if="$form.course?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.course.error?.message }}
                    </Message>
                </div>

                <div class="form-field">
                    <label for="region" class="form-label">Region</label>
                    <Select
                        v-model="initialValues.region"
                        :options="philippineRegions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select your region"
                        class="form-select"
                        :highlightOnSelect="false"
                    />
                    <Message
                        v-if="$form.region?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.region.error?.message }}
                    </Message>
                </div>
            </div>

            <div class="form-section-divider">
                <span>Address Details</span>
            </div>

            <div class="form-field">
                <label for="addressLine" class="form-label">Street / Building Address</label>
                <InputText
                    id="addressLine"
                    name="addressLine"
                    type="text"
                    placeholder="e.g. 123 Rizal Street"
                    class="form-input"
                />
                <Message
                    v-if="$form.addressLine?.invalid"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ $form.addressLine.error?.message }}
                </Message>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="barangay" class="form-label">Barangay</label>
                    <Select
                        id="barangay"
                        name="barangay"
                        v-model="initialValues.barangay"
                        :options="barangaysForCity(initialValues.cityMunicipality)"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select barangay"
                        class="form-select"
                        :highlightOnSelect="false"
                        showClear
                        filter
                    />
                    <Message
                        v-if="$form.barangay?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.barangay.error?.message }}
                    </Message>
                </div>

                <div class="form-field">
                    <label for="cityMunicipality" class="form-label">City / Municipality</label>
                    <Select
                        v-model="initialValues.cityMunicipality"
                        :options="citiesForRegion(initialValues.region)"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select city / municipality"
                        class="form-select"
                        :highlightOnSelect="false"
                        showClear
                        filter
                    />
                    <Message
                        v-if="$form.cityMunicipality?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.cityMunicipality.error?.message }}
                    </Message>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="province" class="form-label">Province</label>
                    <InputText
                        id="province"
                        name="province"
                        type="text"
                        placeholder="e.g. Metro Manila"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.province?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.province.error?.message }}
                    </Message>
                </div>

                <div class="form-field">
                    <label for="postalCode" class="form-label">Postal Code</label>
                    <InputText
                        id="postalCode"
                        name="postalCode"
                        type="text"
                        placeholder="e.g. 1103"
                        class="form-input"
                    />
                    <Message
                        v-if="$form.postalCode?.invalid"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ $form.postalCode.error?.message }}
                    </Message>
                </div>
            </div>

            <div class="form-options">
                <div class="checkbox-field">
                    <Checkbox name="agreeToTerms" binary inputId="agreeToTerms" />
                    <label for="agreeToTerms" class="checkbox-label">
                        I agree to the <a href="#" class="link">Terms of Service</a> and <a href="#" class="link">Privacy Policy</a>
                    </label>
                </div>
            </div>

            <Button
                type="submit"
                severity="secondary"
                label="Create Account"
                class="submit-btn"
            />
        </Form>

        <!-- Sign in prompt with smooth transition -->
        <div class="signin-wrapper">
            <p class="signin-prompt">
                Already have an account?
                <a 
                    href="/login" 
                    class="signin-link"
                    @click.prevent="router.push('/login')"
                >
                    Sign in
                </a>
            </p>
        </div>
    </div>
</template>

<style scoped>
.register-form-wrapper {
    width: 100%;
    max-width: 480px;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin: 0 auto;
}

/* Back Button */
.back-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0;
    background: none;
    border: none;
    color: var(--muted-text);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: color 0.2s;
}

.back-button:hover {
    color: #111;
}

.back-button i {
    font-size: 0.75rem;
}

/* Form Header */
.form-header {
    text-align: center;
    margin-bottom: 0.5rem;
}

.form-title {
    font-size: 2rem;
    font-weight: 700;
    color: #111;
    margin: 0 0 0.5rem 0;
}

.form-subtitle {
    font-size: 1rem;
    color: var(--muted-text);
    margin: 0;
}

/* Form Layout */
.register-form {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    width: 100%;
}

/* Two-column row */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

/* Section Divider */
.form-section-divider {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 0.75rem 0;
}

.form-section-divider::before,
.form-section-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--surface-border);
}

.form-section-divider span {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted-text);
    white-space: nowrap;
}

/* Role Selector */
.form-field:first-child {
    margin-bottom: 0.25rem;
}

.role-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    width: 100%;
}

.role-option {
    position: relative;
    cursor: pointer;
    border: 2px solid var(--surface-border);
    border-radius: 12px;
    padding: 0.5rem;
    transition: all 0.3s ease;
    background: var(--surface-bg);
}

.role-option:hover {
    border-color: var(--button-primary-bg);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.role-option.active {
    border-color: var(--button-primary-bg);
    background: var(--button-primary-bg);
}

.role-option.active .role-content {
    color: var(--button-primary-text);
}

.role-option.active .role-content i {
    color: var(--button-primary-text);
}

.hidden-radio {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.role-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
    color: var(--page-text);
    font-weight: 600;
    font-size: 0.875rem;
    transition: color 0.3s ease;
}

.role-content i {
    font-size: 1.25rem;
    color: var(--muted-text);
    transition: color 0.3s ease;
}

/* Regular Form Fields */
.form-field {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    width: 100%;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111;
    letter-spacing: 0.02em;
}

.form-input,
.form-select {
    width: 100%;
}

/* Native select — matches .p-inputtext */
.form-select {
    padding: 0.75rem 0.875rem;
    border: 1.5px solid var(--surface-border);
    border-radius: 12px;
    background: var(--surface-bg);
    color: var(--page-text);
    font-size: 0.875rem;
    font-weight: 400;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    appearance: auto;
    -webkit-appearance: auto;
    cursor: pointer;
    min-height: 44px;
    line-height: 1.5;
}

.form-select:focus {
    outline: none;
    border-color: #111;
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.08);
    background: #fff;
}

/* PrimeVue Select — override theme defaults to match InputText exactly */
:deep(.p-select) {
    width: 100%;
    min-height: 44px;
}

:deep(.p-select .p-select-label) {
    padding: 0.75rem 0.875rem !important;
    font-size: 0.875rem !important;
    font-weight: 400 !important;
    line-height: 1.5 !important;
    min-height: 44px !important;
    border-radius: 12px;
    background: var(--surface-bg) !important;
    border: 1.5px solid var(--surface-border) !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
    color: var(--page-text) !important;
    transition: all 0.2s ease !important;
}

:deep(.p-select:has(.p-select-label.p-placeholder) .p-select-label) {
    color: var(--muted-text) !important;
}

:deep(.p-select.p-filled .p-select-label),
:deep(.p-select:not(.p-variant-filled) .p-select-label) {
    color: var(--page-text) !important;
}

:deep(.p-select:focus-within .p-select-label),
:deep(.p-select.p-focus .p-select-label) {
    border-color: #111 !important;
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.08) !important;
    background: #fff !important;
    outline: none !important;
}

:deep(.p-select-label:focus) {
    border-color: #111 !important;
}

/* Remove inner input that breaks layout */
:deep(.p-select .p-hidden-accessible),
:deep(.p-select .p-hidden-accessible input) {
    display: none !important;
}

:deep(.p-select.p-invalid .p-select-label) {
    border-color: #e53935 !important;
}

:deep(.p-inputtext),
:deep(.p-password input) {
    width: 100%;
    padding: 0.75rem 0.875rem;
    border: 1.5px solid var(--surface-border);
    border-radius: 12px;
    background: var(--surface-bg);
    color: var(--page-text);
    font-size: 0.875rem;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

:deep(.p-inputtext:focus),
:deep(.p-password-input:focus) {
    outline: none;
    border-color: #111;
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.08);
    background: #fff;
}

:deep(.p-password .p-password-input) {
    padding-right: 2.5rem;
}

:deep(.p-password-overlay) {
    border-radius: 12px;
}

:deep(.p-message) {
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
    margin-top: 0.25rem;
}

/* Form Options */
.form-options {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    width: 100%;
}

.checkbox-field {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.checkbox-label {
    font-size: 0.875rem;
    color: var(--muted-text);
    margin: 0;
    user-select: none;
    line-height: 1.4;
}

.link {
    color: var(--button-primary-bg);
    text-decoration: none;
    font-weight: 600;
}

.link:hover {
    text-decoration: underline;
}

/* Submit Button */
.submit-btn {
    width: 100%;
    padding: 0.875rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    border-radius: 12px;
    margin-top: 0.5rem;
    background: #111;
    color: #fff;
    border: 1.5px solid #111;
    transition: all 0.2s ease;
}

.submit-btn:hover {
    background: #222;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

/* Sign In Wrapper with smooth animation */
.signin-wrapper {
    margin-top: 1.5rem;
    text-align: center;
}

.signin-prompt {
    font-size: 0.9375rem;
    color: var(--muted-text);
    margin: 0;
    line-height: 1.5;
    display: inline-block;
}

.signin-link {
    color: var(--button-primary-bg);
    text-decoration: none;
    font-weight: 700;
    position: relative;
    display: inline-block;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.signin-link::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 2px;
    bottom: -2px;
    left: 0;
    background-color: var(--button-primary-bg);
    transform: scaleX(0);
    transform-origin: right;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.signin-link:hover {
    opacity: 0.8;
}

.signin-link:hover::after {
    transform: scaleX(1);
    transform-origin: left;
}

/* Responsive */
@media (max-width: 768px) {
    .register-form-wrapper {
        max-width: 400px;
    }

    .form-title {
        font-size: 1.75rem;
    }

    .back-button {
        font-size: 0.8125rem;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .role-selector {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .form-title {
        font-size: 1.5rem;
    }

    .submit-btn {
        padding: 0.875rem 1rem;
    }
}

/* Dark theme */
:root[data-theme='dark'] .form-title,
:root[data-theme='dark'] .form-label,
:root[data-theme='dark'] .forgot-link,
:root[data-theme='dark'] .submit-btn,
:root[data-theme='dark'] .social-btn,
:root[data-theme='dark'] .signin-link,
:root[data-theme='dark'] .link {
    color: #111;
}

:root[data-theme='dark'] .submit-btn:hover,
:root[data-theme='dark'] .link:hover {
    opacity: 0.7;
}
</style>
