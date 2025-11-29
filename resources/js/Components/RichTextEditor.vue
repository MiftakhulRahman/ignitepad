<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import { watch, onBeforeUnmount } from 'vue';
import {
    Bold, Italic, Strikethrough, Code, List, ListOrdered,
    Quote, Heading1, Heading2, Undo, Redo, Link as LinkIcon
} from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Tulis konten proyekmu di sini...',
    },
});

const emit = defineEmits(['update:modelValue']);

const editor = useEditor({
    extensions: [
        StarterKit.configure({
            // Disable link extension in StarterKit to avoid duplicate with custom Link extension
            link: false,
        }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: { class: 'text-indigo-600 hover:underline cursor-pointer' },
        }),
        Placeholder.configure({
            placeholder: props.placeholder,
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm sm:prose-base max-w-none focus:outline-none min-h-[300px] p-4',
        },
    },
    content: props.modelValue,
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

// Sinkronisasi jika value berubah dari luar
watch(() => props.modelValue, (newValue) => {
    if (editor.value && newValue !== editor.value.getHTML()) {
        editor.value.commands.setContent(newValue, false);
    }
});

onBeforeUnmount(() => {
    if (editor.value) editor.value.destroy();
});

const setLink = () => {
    const previousUrl = editor.value.getAttributes('link').href;
    const url = window.prompt('URL:', previousUrl);
    if (url === null) return;
    if (url === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }
    editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};
</script>

<template>
    <div
        class="border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition-all bg-white">
        <div v-if="editor" class="flex flex-wrap gap-1 p-2 border-b border-gray-200 bg-gray-50">

            <button @click="editor.chain().focus().toggleBold().run()" :class="{ 'is-active': editor.isActive('bold') }"
                class="toolbar-btn" title="Bold">
                <Bold :size="18" />
            </button>
            <button @click="editor.chain().focus().toggleItalic().run()"
                :class="{ 'is-active': editor.isActive('italic') }" class="toolbar-btn" title="Italic">
                <Italic :size="18" />
            </button>
            <button @click="editor.chain().focus().toggleStrike().run()"
                :class="{ 'is-active': editor.isActive('strike') }" class="toolbar-btn" title="Strikethrough">
                <Strikethrough :size="18" />
            </button>

            <div class="w-px h-6 bg-gray-300 mx-1 self-center"></div>

            <button @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }" class="toolbar-btn"
                title="Heading 2">
                <Heading1 :size="18" />
            </button>
            <button @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                :class="{ 'is-active': editor.isActive('heading', { level: 3 }) }" class="toolbar-btn"
                title="Heading 3">
                <Heading2 :size="18" />
            </button>

            <div class="w-px h-6 bg-gray-300 mx-1 self-center"></div>

            <button @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ 'is-active': editor.isActive('bulletList') }" class="toolbar-btn" title="Bullet List">
                <List :size="18" />
            </button>
            <button @click="editor.chain().focus().toggleOrderedList().run()"
                :class="{ 'is-active': editor.isActive('orderedList') }" class="toolbar-btn" title="Numbered List">
                <ListOrdered :size="18" />
            </button>
            <button @click="editor.chain().focus().toggleBlockquote().run()"
                :class="{ 'is-active': editor.isActive('blockquote') }" class="toolbar-btn" title="Quote">
                <Quote :size="18" />
            </button>
            <button @click="editor.chain().focus().toggleCodeBlock().run()"
                :class="{ 'is-active': editor.isActive('codeBlock') }" class="toolbar-btn" title="Code Block">
                <Code :size="18" />
            </button>
            <button @click="setLink" :class="{ 'is-active': editor.isActive('link') }" class="toolbar-btn" title="Link">
                <LinkIcon :size="18" />
            </button>

            <div class="w-px h-6 bg-gray-300 mx-1 self-center"></div>

            <button @click="editor.chain().focus().undo().run()" :disabled="!editor.can().undo()" class="toolbar-btn"
                title="Undo">
                <Undo :size="18" />
            </button>
            <button @click="editor.chain().focus().redo().run()" :disabled="!editor.can().redo()" class="toolbar-btn"
                title="Redo">
                <Redo :size="18" />
            </button>
        </div>

        <EditorContent :editor="editor" />
    </div>
</template>

<style scoped>
.toolbar-btn {
    @apply p-2 rounded text-gray-600 hover:bg-gray-200 transition-colors focus:outline-none;
}

.toolbar-btn.is-active {
    @apply bg-indigo-100 text-indigo-600 font-bold;
}

.toolbar-btn:disabled {
    @apply opacity-50 cursor-not-allowed hover:bg-transparent;
}

/* Placeholder styling via TipTap class */
:deep(.tiptap p.is-editor-empty:first-child::before) {
    content: attr(data-placeholder);
    float: left;
    color: #9ca3af;
    pointer-events: none;
    height: 0;
}
</style>