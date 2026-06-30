<script setup>
import { computed, nextTick, onMounted, onBeforeUnmount, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import RadioButton from 'primevue/radiobutton';
import Checkbox from 'primevue/checkbox';
import ProgressBar from 'primevue/progressbar';
import Tag from 'primevue/tag';

const route = useRoute();
const router = useRouter();
const token = route.params.id;
const taskId = route.params.taskId;

const loading = ref(true);
const error = ref('');
const task = ref(null);
const answers = ref({});
const logs = ref([]);
const order = ref([]);
const current = ref(0);
const submitted = ref(false);
const submitting = ref(false);
const showAnswers = ref(false);
const solution = ref(null);
const aiResult = ref(null);
const myScore = ref(null);
const objectiveTotal = ref(0);

let proctorAttached = false;
let saveTimer = null;

// ---- Popular code starter templates ----
const codeTemplates = {
    php: "<?php\n\nclass Solution\n{\n    public function solve()\n    {\n        // Your code here\n    }\n}\n",
    python: "class Solution:\n    def solve(self):\n        # Your code here\n        pass\n",
    javascript: "class Solution {\n  solve() {\n    // Your code here\n  }\n}\n",
    typescript: "class Solution {\n  solve(): void {\n    // Your code here\n  }\n}\n",
    java: "public class Solution {\n    public static void main(String[] args) {\n        // Your code here\n    }\n}\n",
    c: "#include <stdio.h>\n\nint main(void) {\n    // Your code here\n    return 0;\n}\n",
    cpp: "#include <iostream>\nusing namespace std;\n\nint main() {\n    // Your code here\n    return 0;\n}\n",
    csharp: "using System;\n\nclass Solution {\n    static void Main() {\n        // Your code here\n    }\n}\n",
    html: "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Document</title>\n</head>\n<body>\n    <!-- Your code here -->\n</body>\n</html>\n",
    css: "/* Your styles here */\nbody {\n}\n",
    sql: "-- Your query here\nSELECT * FROM table_name;\n",
    ruby: "class Solution\n  def solve\n    # Your code here\n  end\nend\n",
    go: "package main\n\nimport \"fmt\"\n\nfunc main() {\n    // Your code here\n}\n",
    kotlin: "fun main() {\n    // Your code here\n}\n",
    swift: "import Foundation\n\nfunc solve() {\n    // Your code here\n}\n",
    plaintext: '',
};
const languageLabels = {
    php: 'PHP', python: 'Python', javascript: 'JavaScript', typescript: 'TypeScript',
    java: 'Java', c: 'C', cpp: 'C++', csharp: 'C#', html: 'HTML', css: 'CSS',
    sql: 'SQL', ruby: 'Ruby', go: 'Go', kotlin: 'Kotlin', swift: 'Swift', plaintext: 'Plain text',
};

const orderedQuestions = computed(() => {
    if (!task.value) return [];
    const qs = task.value.questions || [];
    return (order.value.length ? order.value : qs.map((_, i) => i)).map((qi) => ({ qi, q: qs[qi] }));
});
const total = computed(() => orderedQuestions.value.length);
const currentItem = computed(() => orderedQuestions.value[current.value] || null);
const progress = computed(() => total.value ? Math.round(((current.value + 1) / total.value) * 100) : 0);
const answeredCount = computed(() => orderedQuestions.value.filter(({ qi, q }) => {
    const a = answers.value[qi];
    return q.type === 'checkbox' ? Array.isArray(a) && a.length : (a !== '' && a != null);
}).length);

const pushLog = (type, detail) => { logs.value.push({ type, detail, at: new Date().toISOString() }); };

const autosave = () => {
    if (!task.value || submitted.value) return;
    clearTimeout(saveTimer);
    saveTimer = setTimeout(() => {
        axios.post(`/api/classrooms/${token}/tasks/${taskId}/submission`, {
            answers: answers.value, logs: logs.value, order: order.value,
        }).catch(() => {});
    }, 700);
};

const onFsChange = () => {
    if (!document.fullscreenElement && task.value?.advanced?.fullscreen && task.value?.advanced?.fs_exit && !submitted.value) {
        pushLog('exit_fullscreen', 'Exited fullscreen');
        autosave();
    }
};
const onKeydown = (e) => {
    if (!task.value?.advanced?.fs_shortcuts || submitted.value) return;
    if (e.ctrlKey || e.metaKey || e.altKey) {
        const combo = `${e.ctrlKey ? 'Ctrl+' : ''}${e.metaKey ? 'Meta+' : ''}${e.altKey ? 'Alt+' : ''}${e.key}`;
        pushLog('shortcut', combo);
        autosave();
    }
};
const onVisibility = () => {
    if (document.hidden && task.value && !submitted.value) {
        pushLog('tab_switch', 'Left the exam tab');
        autosave();
    }
};
const onBeforeUnload = () => {
    if (task.value && !submitted.value) localStorage.setItem(`exam_ref_${taskId}`, '1');
};

const attachProctor = () => {
    if (proctorAttached) return;
    document.addEventListener('fullscreenchange', onFsChange);
    document.addEventListener('keydown', onKeydown);
    document.addEventListener('visibilitychange', onVisibility);
    window.addEventListener('beforeunload', onBeforeUnload);
    proctorAttached = true;
};
const detachProctor = () => {
    document.removeEventListener('fullscreenchange', onFsChange);
    document.removeEventListener('keydown', onKeydown);
    document.removeEventListener('visibilitychange', onVisibility);
    window.removeEventListener('beforeunload', onBeforeUnload);
    proctorAttached = false;
};
const enterFullscreen = () => { document.documentElement.requestFullscreen?.().catch(() => {}); };
const exitFullscreen = () => { if (document.fullscreenElement) document.exitFullscreen?.().catch(() => {}); };

const codeLabel = (q) => languageLabels[q.language] || (q.language ? q.language : 'Code');
const starterFor = (q) => q.starter || codeTemplates[q.language] || codeTemplates.plaintext;

const initAnswers = () => {
    const fresh = {};
    (task.value.questions || []).forEach((q, i) => {
        if (q.type === 'checkbox') fresh[i] = [];
        else if (q.type === 'code') fresh[i] = starterFor(q);
        else fresh[i] = '';
    });
    answers.value = fresh;
};

const load = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get(`/api/classrooms/${token}/tasks/${taskId}/take`);
        task.value = data.task;
        order.value = data.order || [];
        showAnswers.value = !!data.show_answers;
        solution.value = data.solution || null;
        initAnswers();

        if (data.submission) {
            answers.value = { ...answers.value, ...(data.submission.answers || {}) };
            logs.value = data.submission.logs || [];
            submitted.value = data.submission.status === 'submitted';
            aiResult.value = data.submission.ai || null;
            myScore.value = data.submission.score;
            objectiveTotal.value = data.submission.objective_total || task.value.objective_total || 0;
        } else {
            objectiveTotal.value = task.value.objective_total || 0;
        }

        if (localStorage.getItem(`exam_ref_${taskId}`)) {
            pushLog('page_refreshed', 'Exam page was refreshed');
            localStorage.removeItem(`exam_ref_${taskId}`);
        }

        await nextTick();
        if (!submitted.value) {
            attachProctor();
            autosave();
            if (task.value.advanced?.fullscreen) enterFullscreen();
        }
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not load this task.';
    } finally {
        loading.value = false;
    }
};

const toggleCheckbox = (qi, text) => {
    const arr = Array.isArray(answers.value[qi]) ? [...answers.value[qi]] : [];
    const idx = arr.indexOf(text);
    if (idx >= 0) arr.splice(idx, 1); else arr.push(text);
    answers.value[qi] = arr;
};

const go = (delta) => {
    const next = current.value + delta;
    if (next >= 0 && next < total.value) current.value = next;
};
const jump = (i) => { current.value = i; };

const submit = async () => {
    if (submitting.value) return;
    if (!confirm('Submit your answers? You will not be able to change them afterwards.')) return;
    submitting.value = true;
    try {
        const { data } = await axios.post(`/api/classrooms/${token}/tasks/${taskId}/submit`, {
            answers: answers.value, logs: logs.value, order: order.value,
        });
        submitted.value = true;
        aiResult.value = data.submission?.ai || null;
        myScore.value = data.submission?.score ?? myScore.value;
        objectiveTotal.value = data.submission?.objective_total || objectiveTotal.value;
        solution.value = data.solution || solution.value;
        if (data.submission) {
            task.value.my_score = data.submission.score;
            task.value.my_status = 'submitted';
        }
        detachProctor();
        exitFullscreen();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (e) {
        alert(e.response?.data?.message || 'Could not submit.');
    } finally {
        submitting.value = false;
    }
};

const leave = () => { router.push(`/dashboard/classes/${token}`); };

const isCorrectOption = (qi, text) => Array.isArray(solution.value?.[qi]) && solution.value[qi].includes(text);
const essayFeedback = (qi) => aiResult.value?.essays?.[qi] || null;
const q_isAnswered = (item) => {
    const a = answers.value?.[item.qi];
    return item.q.type === 'checkbox' ? Array.isArray(a) && a.length : (a !== '' && a != null);
};

watch(answers, () => autosave(), { deep: true });

onMounted(load);
onBeforeUnmount(() => { detachProctor(); clearTimeout(saveTimer); });
</script>

<template>
    <div class="exam">
        <div v-if="loading" class="exam-state"><i class="pi pi-spin pi-spinner"></i> Loading exam…</div>
        <div v-else-if="error" class="exam-state err">{{ error }} <Button label="Back to class" text @click="leave" /></div>

        <template v-else>
            <header class="exam-top">
                <div class="exam-top-left">
                    <button class="exam-back" @click="leave"><i class="pi pi-arrow-left"></i></button>
                    <div>
                        <h1>{{ task.name }}</h1>
                        <p class="exam-sub">
                            <span class="cap">{{ task.type }}</span>
                            <span v-if="task.duration"> · <i class="pi pi-clock"></i> {{ task.duration }} min</span>
                            <span> · {{ total }} question{{ total === 1 ? '' : 's' }}</span>
                        </p>
                    </div>
                </div>
                <Tag v-if="submitted" value="Submitted" severity="success" icon="pi pi-check" />
                <span v-else class="exam-progress-text">{{ answeredCount }}/{{ total }} answered</span>
            </header>

            <ProgressBar v-if="!submitted" :value="progress" :showValue="false" class="exam-bar" />

            <!-- Submitted summary -->
            <div v-if="submitted" class="exam-done">
                <div class="done-card">
                    <i class="pi pi-check-circle"></i>
                    <h2>Your answers were submitted</h2>
                    <div v-if="objectiveTotal" class="score-badge">
                        <span class="score-num">{{ myScore ?? 0 }}<span class="score-den">/{{ objectiveTotal }}</span></span>
                        <span class="score-label">Auto-graded score</span>
                    </div>
                    <p v-else class="muted">This task has no auto-graded questions. Your teacher will review it.</p>
                    <Button label="Back to class" icon="pi pi-arrow-left" @click="leave" />
                </div>

                <div v-if="showAnswers" class="review">
                    <h3>Answer review</h3>
                    <div v-for="({ qi, q }, idx) in orderedQuestions" :key="qi" class="review-q">
                        <div class="review-head"><span class="rq-num">{{ idx + 1 }}</span><span>{{ q.name }}</span></div>

                        <div v-if="q.type === 'radio' || q.type === 'checkbox'" class="review-opts">
                            <div v-for="(o, oi) in q.options" :key="oi" class="review-opt" :class="{ good: isCorrectOption(qi, o.text), chosen: q.type === 'checkbox' ? (answers[qi] || []).includes(o.text) : answers[qi] === o.text }">
                                <i :class="isCorrectOption(qi, o.text) ? 'pi pi-check' : 'pi pi-circle'"></i>
                                <span>{{ o.text }}</span>
                            </div>
                        </div>
                        <div v-else-if="q.type === 'identification'" class="review-id">
                            <p><span class="muted">Your answer:</span> {{ answers[qi] || '—' }}</p>
                            <p v-if="solution && solution[qi] != null"><span class="muted">Correct:</span> <strong>{{ solution[qi] }}</strong></p>
                        </div>
                        <div v-else-if="q.type === 'essay'" class="review-essay">
                            <pre class="ans-pre">{{ answers[qi] || '—' }}</pre>
                            <div v-if="essayFeedback(qi)" class="ai-box">
                                <i class="pi pi-sparkles"></i>
                                <div>
                                    <strong>AI score: {{ essayFeedback(qi).score }}/100</strong>
                                    <span class="ai-engine">{{ essayFeedback(qi).engine === 'ai' ? 'AI graded' : 'Auto graded' }}</span>
                                    <p>{{ essayFeedback(qi).feedback }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="review-essay">
                            <pre class="ans-pre code">{{ answers[qi] || '—' }}</pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active exam: one question per page -->
            <div v-else-if="currentItem" class="exam-body">
                <div class="q-nav">
                    <button v-for="(item, i) in orderedQuestions" :key="item.qi" class="q-dot"
                        :class="{ active: i === current, done: q_isAnswered(item) }" @click="jump(i)">{{ i + 1 }}</button>
                </div>

                <div class="q-stage">
                    <div class="q-label">Question {{ current + 1 }} of {{ total }}</div>
                    <h2 class="q-title">{{ currentItem.q.name }}</h2>

                    <!-- Radio -->
                    <div v-if="currentItem.q.type === 'radio'" class="opt-list">
                        <label v-for="(o, oi) in currentItem.q.options" :key="oi" class="opt" :class="{ sel: answers[currentItem.qi] === o.text }">
                            <RadioButton v-model="answers[currentItem.qi]" :inputId="`o${current}_${oi}`" :value="o.text" />
                            <span>{{ o.text }}</span>
                        </label>
                    </div>

                    <!-- Checkbox -->
                    <div v-else-if="currentItem.q.type === 'checkbox'" class="opt-list">
                        <label v-for="(o, oi) in currentItem.q.options" :key="oi" class="opt" :class="{ sel: (answers[currentItem.qi] || []).includes(o.text) }">
                            <Checkbox :modelValue="(answers[currentItem.qi] || []).includes(o.text)" :binary="true" @update:modelValue="toggleCheckbox(currentItem.qi, o.text)" />
                            <span>{{ o.text }}</span>
                        </label>
                    </div>

                    <!-- Identification -->
                    <div v-else-if="currentItem.q.type === 'identification'" class="id-wrap">
                        <InputText v-model="answers[currentItem.qi]" placeholder="Type your answer…" class="id-input" />
                    </div>

                    <!-- Essay -->
                    <div v-else-if="currentItem.q.type === 'essay'">
                        <Textarea v-model="answers[currentItem.qi]" rows="8" autoResize placeholder="Write your answer…" class="essay-input" />
                    </div>

                    <!-- Code -->
                    <div v-else-if="currentItem.q.type === 'code'" class="code-wrap">
                        <div class="code-head">
                            <span class="code-lang"><i class="pi pi-code"></i> {{ codeLabel(currentItem.q) }}</span>
                            <button class="code-reset" type="button" @click="answers[currentItem.qi] = starterFor(currentItem.q)"><i class="pi pi-replay"></i> Reset template</button>
                        </div>
                        <textarea v-model="answers[currentItem.qi]" class="code-editor" spellcheck="false" wrap="off"></textarea>
                    </div>

                    <div v-else>
                        <Textarea v-model="answers[currentItem.qi]" rows="6" autoResize placeholder="Your answer…" />
                    </div>
                </div>

                <div class="q-actions">
                    <Button label="Previous" icon="pi pi-chevron-left" outlined :disabled="current === 0" @click="go(-1)" />
                    <Button v-if="current < total - 1" label="Next" icon="pi pi-chevron-right" iconPos="right" @click="go(1)" />
                    <Button v-else label="Submit exam" icon="pi pi-check" severity="success" :loading="submitting" @click="submit" />
                </div>

                <p v-if="task.advanced?.fullscreen || task.advanced?.fs_exit || task.advanced?.fs_shortcuts" class="proctor-note">
                    <i class="pi pi-eye"></i> This session is monitored. Leaving the tab, exiting fullscreen, or using shortcuts is recorded.
                </p>
            </div>
        </template>
    </div>
</template>

<style scoped>
.exam { max-width: 880px; margin: 0 auto; padding: 1.25rem 1rem 4rem; }
.exam-state { padding: 4rem 1rem; text-align: center; color: #64748b; }
.exam-state.err { color: #ef4444; }
.exam-top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: .75rem; }
.exam-top-left { display: flex; align-items: center; gap: .75rem; }
.exam-back { width: 38px; height: 38px; border-radius: 50%; border: 1px solid #e2e8f0; background: #fff; cursor: pointer; color: #334155; }
.exam-back:hover { background: #f1f5f9; }
.exam-top h1 { font-size: 1.3rem; margin: 0; }
.exam-sub { margin: .15rem 0 0; color: #64748b; font-size: .85rem; }
.cap { text-transform: capitalize; }
.exam-progress-text { font-size: .85rem; color: #64748b; font-weight: 600; }
.exam-bar { height: 6px; margin-bottom: 1.5rem; }

.q-nav { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: 1.25rem; }
.q-dot { width: 34px; height: 34px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; cursor: pointer; font-weight: 600; color: #475569; }
.q-dot.done { border-color: var(--accent, #4f46e5); color: var(--accent, #4f46e5); }
.q-dot.active { background: var(--accent, #4f46e5); border-color: var(--accent, #4f46e5); color: #fff; }

.q-stage { background: #fff; border: 1px solid #e9edf3; border-radius: 16px; padding: 1.5rem; box-shadow: 0 6px 22px rgba(15,23,42,.05); }
.q-label { font-size: .78rem; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; font-weight: 700; }
.q-title { font-size: 1.15rem; margin: .35rem 0 1.25rem; line-height: 1.5; }

.opt-list { display: flex; flex-direction: column; gap: .6rem; }
.opt { display: flex; align-items: center; gap: .7rem; padding: .8rem 1rem; border: 1px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: .15s; }
.opt:hover { border-color: #cbd5e1; background: #f8fafc; }
.opt.sel { border-color: var(--accent, #4f46e5); background: color-mix(in srgb, var(--accent, #4f46e5) 8%, #fff); }

.id-input, .essay-input { width: 100%; }
.id-input { max-width: 480px; }

.code-wrap { border: 1px solid #1e293b; border-radius: 12px; overflow: hidden; background: #0f172a; }
.code-head { display: flex; align-items: center; justify-content: space-between; padding: .5rem .8rem; background: #1e293b; }
.code-lang { color: #e2e8f0; font-size: .82rem; font-weight: 600; }
.code-reset { background: transparent; border: none; color: #94a3b8; cursor: pointer; font-size: .78rem; }
.code-reset:hover { color: #e2e8f0; }
.code-editor { width: 100%; min-height: 320px; border: none; outline: none; resize: vertical;
    background: #0f172a; color: #e2e8f0; font-family: 'Fira Code', Consolas, monospace; font-size: .9rem; line-height: 1.55; padding: 1rem; tab-size: 4; }

.q-actions { display: flex; justify-content: space-between; gap: .75rem; margin-top: 1.25rem; }
.q-actions :deep(.p-button) { min-width: 130px; }
.proctor-note { margin-top: 1rem; font-size: .8rem; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; padding: .55rem .8rem; border-radius: 10px; }

.exam-done { display: flex; flex-direction: column; gap: 1.5rem; }
.done-card { text-align: center; background: #fff; border: 1px solid #e9edf3; border-radius: 16px; padding: 2.5rem 1.5rem; }
.done-card .pi-check-circle { font-size: 3rem; color: #10b981; }
.done-card h2 { margin: .75rem 0 .35rem; }
.done-card p { color: #475569; margin-bottom: 1.25rem; }
.score-badge { display: inline-flex; flex-direction: column; align-items: center; gap: .2rem; margin: .5rem 0 1.25rem; padding: .9rem 1.6rem; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 14px; }
.score-num { font-size: 2.2rem; font-weight: 800; color: #047857; line-height: 1; }
.score-den { font-size: 1.1rem; font-weight: 600; color: #10b981; }
.score-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; color: #059669; font-weight: 700; }

.review h3 { margin: 0 0 1rem; }
.review-q { background: #fff; border: 1px solid #e9edf3; border-radius: 14px; padding: 1.1rem 1.25rem; margin-bottom: 1rem; }
.review-head { display: flex; gap: .6rem; align-items: flex-start; font-weight: 600; margin-bottom: .75rem; }
.rq-num { background: var(--accent, #4f46e5); color: #fff; width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: .8rem; flex: 0 0 auto; }
.review-opts { display: flex; flex-direction: column; gap: .4rem; }
.review-opt { display: flex; align-items: center; gap: .55rem; padding: .5rem .7rem; border-radius: 8px; border: 1px solid #eef2f7; color: #475569; }
.review-opt.good { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
.review-opt.chosen { font-weight: 600; }
.review-id p, .muted { margin: .2rem 0; }
.muted { color: #94a3b8; }
.ans-pre { white-space: pre-wrap; background: #f8fafc; border: 1px solid #eef2f7; border-radius: 8px; padding: .8rem; margin: 0; font-family: inherit; }
.ans-pre.code { font-family: 'Fira Code', Consolas, monospace; font-size: .85rem; }
.ai-box { display: flex; gap: .6rem; margin-top: .75rem; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 10px; padding: .75rem .9rem; }
.ai-box .pi-sparkles { color: #4f46e5; }
.ai-engine { display: inline-block; margin-left: .5rem; font-size: .7rem; background: #4f46e5; color: #fff; padding: .05rem .4rem; border-radius: 6px; }
.ai-box p { margin: .35rem 0 0; color: #3730a3; font-size: .85rem; }
</style>
