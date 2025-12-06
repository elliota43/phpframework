import React, { useEffect, useState, useCallback, useMemo } from 'react';
import ReactDOM from 'react-dom/client';
import {
    DndContext,
    closestCenter,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
    DragOverlay,
} from '@dnd-kit/core';
import {
    arrayMove,
    SortableContext,
    sortableKeyboardCoordinates,
    verticalListSortingStrategy,
    useSortable,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { format, formatDistanceToNow, isPast, isToday, isTomorrow } from 'date-fns';
import {
    Plus, X, Edit2, Trash2, Search, Filter, Calendar, User, 
    AlertCircle, CheckCircle2, Clock, MoreVertical, GripVertical,
    ChevronLeft, ChevronRight
} from 'lucide-react';
import '../css/app.css';

// ============= Utility Functions =============

function classNames(...parts) {
    return parts.filter(Boolean).join(' ');
}

function cn(...classes) {
    return classes.filter(Boolean).join(' ');
}

const priorityConfig = {
    low: { label: 'Low', color: '#94a3b8', bg: 'rgba(148, 163, 184, 0.1)', border: 'rgba(148, 163, 184, 0.3)' },
    medium: { label: 'Medium', color: '#3b82f6', bg: 'rgba(59, 130, 246, 0.1)', border: 'rgba(59, 130, 246, 0.3)' },
    high: { label: 'High', color: '#f97316', bg: 'rgba(249, 115, 22, 0.1)', border: 'rgba(249, 115, 22, 0.3)' },
    urgent: { label: 'Urgent', color: '#ef4444', bg: 'rgba(239, 68, 68, 0.1)', border: 'rgba(239, 68, 68, 0.3)' },
};

function getPriorityConfig(priority) {
    return priorityConfig[priority?.toLowerCase()] || priorityConfig.medium;
}

function formatDueDate(dateString) {
    if (!dateString) return null;
    try {
        const date = new Date(dateString);
        if (isPast(date) && !isToday(date)) {
            return { text: formatDistanceToNow(date, { addSuffix: true }), overdue: true };
        }
        if (isToday(date)) {
            return { text: 'Today', overdue: false, today: true };
        }
        if (isTomorrow(date)) {
            return { text: 'Tomorrow', overdue: false };
        }
        return { text: format(date, 'MMM d, yyyy'), overdue: false };
    } catch {
        return null;
    }
}

// ============= API Helpers =============

async function apiRequest(url, options = {}) {
    const response = await fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            ...options.headers,
        },
        ...options,
    });
    
    if (!response.ok) {
        const error = await response.json().catch(() => ({}));
        throw new Error(error.error || `HTTP ${response.status}`);
    }
    
    return response.json();
}

// ============= Modal Component =============

function Modal({ isOpen, onClose, title, children, size = 'md' }) {
    if (!isOpen) return null;

    const sizeClasses = {
        sm: 'max-w-md',
        md: 'max-w-2xl',
        lg: 'max-w-4xl',
        xl: 'max-w-6xl',
    };

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center p-4"
            style={{
                backgroundColor: 'rgba(0, 0, 0, 0.75)',
                backdropFilter: 'blur(4px)',
            }}
            onClick={onClose}
        >
            <div
                className={cn(
                    'relative w-full rounded-2xl shadow-2xl',
                    'bg-gradient-to-br from-slate-900 to-slate-800',
                    'border border-slate-700',
                    sizeClasses[size],
                    'max-h-[90vh] overflow-hidden flex flex-col'
                )}
                onClick={(e) => e.stopPropagation()}
            >
                <div className="flex items-center justify-between p-6 border-b border-slate-700">
                    <h2 className="text-xl font-semibold text-white">{title}</h2>
                    <button
                        onClick={onClose}
                        className="p-2 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-white transition-colors"
                    >
                        <X size={20} />
                    </button>
                </div>
                <div className="flex-1 overflow-y-auto p-6">{children}</div>
            </div>
        </div>
    );
}

// ============= Task Edit Modal =============

function TaskEditModal({ task, isOpen, onClose, onSave, boardId, columnId: initialColumnId, columns = [] }) {
    const [formData, setFormData] = useState({
        title: '',
        description: '',
        priority: 'medium',
        status: 'open',
        assignee: '',
        due_date: '',
        column_id: null,
    });
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        if (task) {
            setFormData({
                title: task.title || '',
                description: task.description || '',
                priority: task.priority || 'medium',
                status: task.status || 'open',
                assignee: task.assignee || '',
                due_date: task.due_date ? task.due_date.split('T')[0] : '',
                column_id: task.column_id || null,
            });
        } else {
            // Reset for new task
            setFormData({
                title: '',
                description: '',
                priority: 'medium',
                status: 'open',
                assignee: '',
                due_date: '',
                column_id: initialColumnId || (columns.length > 0 ? columns[0].id : null),
            });
        }
    }, [task, initialColumnId, columns]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (task) {
                // Update existing task
                await apiRequest(`/api/kanban/tasks/${task.id}`, {
                    method: 'PUT',
                    body: JSON.stringify(formData),
                });
            } else {
                // Create new task - need column_id
                const columnId = formData.column_id;
                await apiRequest(`/api/kanban/boards/${boardId}/tasks`, {
                    method: 'POST',
                    body: JSON.stringify({
                        ...formData,
                        column_id: columnId,
                    }),
                });
            }
            onSave();
            onClose();
        } catch (error) {
            alert(error.message || 'Failed to save task');
        } finally {
            setSaving(false);
        }
    };

    return (
        <Modal isOpen={isOpen} onClose={onClose} title={task ? 'Edit Task' : 'New Task'}>
            <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                    <label className="block text-sm font-medium text-slate-300 mb-2">
                        Title *
                    </label>
                    <input
                        type="text"
                        value={formData.title}
                        onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                        className="w-full px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Task title"
                        required
                    />
                </div>

                <div>
                    <label className="block text-sm font-medium text-slate-300 mb-2">
                        Description
                    </label>
                    <textarea
                        value={formData.description}
                        onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                        rows={4}
                        className="w-full px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Task description"
                    />
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-slate-300 mb-2">
                            Priority
                        </label>
                        <select
                            value={formData.priority}
                            onChange={(e) => setFormData({ ...formData, priority: e.target.value })}
                            className="w-full px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            {Object.entries(priorityConfig).map(([key, config]) => (
                                <option key={key} value={key}>
                                    {config.label}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-300 mb-2">
                            Due Date
                        </label>
                        <input
                            type="date"
                            value={formData.due_date}
                            onChange={(e) => setFormData({ ...formData, due_date: e.target.value })}
                            className="w-full px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-slate-300 mb-2">
                            Column {!task && '*'}
                        </label>
                        {!task && columns.length > 0 ? (
                            <select
                                value={formData.column_id || ''}
                                onChange={(e) => setFormData({ ...formData, column_id: parseInt(e.target.value) })}
                                className="w-full px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required={!task}
                            >
                                {columns.map((col) => (
                                    <option key={col.id} value={col.id}>
                                        {col.name}
                                    </option>
                                ))}
                            </select>
                        ) : (
                            <input
                                type="text"
                                value={columns.find(c => c.id === formData.column_id)?.name || ''}
                                className="w-full px-4 py-2 rounded-lg bg-slate-700 border border-slate-700 text-slate-400 cursor-not-allowed"
                                disabled
                            />
                        )}
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-slate-300 mb-2">
                            Assignee
                        </label>
                        <input
                            type="text"
                            value={formData.assignee}
                            onChange={(e) => setFormData({ ...formData, assignee: e.target.value })}
                            className="w-full px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Assignee name"
                        />
                    </div>
                </div>

                <div className="flex justify-end gap-3 pt-4 border-t border-slate-700">
                    <button
                        type="button"
                        onClick={onClose}
                        className="px-4 py-2 rounded-lg bg-slate-700 text-white hover:bg-slate-600 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        disabled={saving || !formData.title.trim()}
                        className="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {saving ? 'Saving...' : task ? 'Update' : 'Create'}
                    </button>
                </div>
            </form>
        </Modal>
    );
}

// ============= Sortable Task Card =============

function SortableTaskCard({ task, onEdit, onDelete, priorityConfig }) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id: task.id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    const dueDate = formatDueDate(task.due_date);
    const priority = getPriorityConfig(task.priority);

    return (
        <div
            ref={setNodeRef}
            style={style}
            className={cn(
                'group relative bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-4',
                'border border-slate-700 shadow-lg',
                'hover:border-slate-600 hover:shadow-xl transition-all duration-200',
                isDragging && 'ring-2 ring-blue-500'
            )}
        >
            <div className="flex items-start justify-between gap-2 mb-2">
                <h3 className="text-sm font-semibold text-white flex-1 leading-tight">
                    {task.title}
                </h3>
                <div
                    {...attributes}
                    {...listeners}
                    className="opacity-0 group-hover:opacity-100 cursor-grab active:cursor-grabbing p-1 text-slate-400 hover:text-slate-300 transition-opacity"
                >
                    <GripVertical size={16} />
                </div>
            </div>

            {task.description && (
                <p className="text-xs text-slate-400 mb-3 line-clamp-2">
                    {task.description}
                </p>
            )}

            <div className="flex flex-wrap items-center gap-2 mb-3">
                <span
                    className="px-2 py-1 text-xs font-medium rounded-full"
                    style={{
                        color: priority.color,
                        backgroundColor: priority.bg,
                        border: `1px solid ${priority.border}`,
                    }}
                >
                    {priority.label}
                </span>

                {dueDate && (
                    <span
                        className={cn(
                            'px-2 py-1 text-xs rounded-full flex items-center gap-1',
                            dueDate.overdue
                                ? 'bg-red-500/20 text-red-400 border border-red-500/30'
                                : dueDate.today
                                ? 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30'
                                : 'bg-slate-700 text-slate-300 border border-slate-600'
                        )}
                    >
                        <Calendar size={12} />
                        {dueDate.text}
                    </span>
                )}

                {task.assignee && (
                    <span className="px-2 py-1 text-xs rounded-full bg-slate-700 text-slate-300 border border-slate-600 flex items-center gap-1">
                        <User size={12} />
                        {task.assignee}
                    </span>
                )}
            </div>

            <div className="flex items-center justify-between pt-2 border-t border-slate-700">
                <span className="text-xs text-slate-500">#{task.id}</span>
                <div className="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button
                        onClick={() => onEdit(task)}
                        className="p-1.5 rounded hover:bg-slate-700 text-slate-400 hover:text-blue-400 transition-colors"
                        title="Edit"
                    >
                        <Edit2 size={14} />
                    </button>
                    <button
                        onClick={() => onDelete(task.id)}
                        className="p-1.5 rounded hover:bg-slate-700 text-slate-400 hover:text-red-400 transition-colors"
                        title="Delete"
                    >
                        <Trash2 size={14} />
                    </button>
                </div>
            </div>
        </div>
    );
}

// ============= Task Card (Non-sortable) =============

function TaskCard({ task, onEdit, onDelete, priorityConfig }) {
    const dueDate = formatDueDate(task.due_date);
    const priority = getPriorityConfig(task.priority);

    return (
        <div className="group relative bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-4 border border-slate-700 shadow-lg hover:border-slate-600 hover:shadow-xl transition-all duration-200">
            <h3 className="text-sm font-semibold text-white mb-2 leading-tight">
                {task.title}
            </h3>

            {task.description && (
                <p className="text-xs text-slate-400 mb-3 line-clamp-2">
                    {task.description}
                </p>
            )}

            <div className="flex flex-wrap items-center gap-2 mb-3">
                <span
                    className="px-2 py-1 text-xs font-medium rounded-full"
                    style={{
                        color: priority.color,
                        backgroundColor: priority.bg,
                        border: `1px solid ${priority.border}`,
                    }}
                >
                    {priority.label}
                </span>

                {dueDate && (
                    <span
                        className={cn(
                            'px-2 py-1 text-xs rounded-full flex items-center gap-1',
                            dueDate.overdue
                                ? 'bg-red-500/20 text-red-400 border border-red-500/30'
                                : dueDate.today
                                ? 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30'
                                : 'bg-slate-700 text-slate-300 border border-slate-600'
                        )}
                    >
                        <Calendar size={12} />
                        {dueDate.text}
                    </span>
                )}

                {task.assignee && (
                    <span className="px-2 py-1 text-xs rounded-full bg-slate-700 text-slate-300 border border-slate-600 flex items-center gap-1">
                        <User size={12} />
                        {task.assignee}
                    </span>
                )}
            </div>

            <div className="flex items-center justify-between pt-2 border-t border-slate-700">
                <span className="text-xs text-slate-500">#{task.id}</span>
                <div className="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button
                        onClick={() => onEdit(task)}
                        className="p-1.5 rounded hover:bg-slate-700 text-slate-400 hover:text-blue-400 transition-colors"
                        title="Edit"
                    >
                        <Edit2 size={14} />
                    </button>
                    <button
                        onClick={() => onDelete(task.id)}
                        className="p-1.5 rounded hover:bg-slate-700 text-slate-400 hover:text-red-400 transition-colors"
                        title="Delete"
                    >
                        <Trash2 size={14} />
                    </button>
                </div>
            </div>
        </div>
    );
}

// ============= Kanban Column =============

function KanbanColumn({ column, tasks, onAddTask, onEditTask, onDeleteTask, searchQuery, filterPriority }) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id: `column-${column.id}` });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    };

    const filteredTasks = useMemo(() => {
        let filtered = tasks;

        if (searchQuery) {
            const query = searchQuery.toLowerCase();
            filtered = filtered.filter(
                (task) =>
                    task.title?.toLowerCase().includes(query) ||
                    task.description?.toLowerCase().includes(query)
            );
        }

        if (filterPriority) {
            filtered = filtered.filter((task) => task.priority === filterPriority);
        }

        return filtered;
    }, [tasks, searchQuery, filterPriority]);

    const taskIds = filteredTasks.map((task) => task.id);

    return (
        <div
            ref={setNodeRef}
            style={style}
            className={cn(
                'flex-shrink-0 w-80 bg-gradient-to-b from-slate-900 to-slate-800 rounded-2xl p-4',
                'border border-slate-700 shadow-2xl',
                'flex flex-col h-full max-h-[calc(100vh-12rem)]',
                isDragging && 'opacity-50'
            )}
        >
            <div className="flex items-center justify-between mb-4">
                <div className="flex items-center gap-2">
                    <h2 className="text-base font-semibold text-white">{column.name}</h2>
                    <span className="px-2 py-1 text-xs font-medium rounded-full bg-slate-700 text-slate-300">
                        {filteredTasks.length}
                    </span>
                </div>
                <button
                    onClick={() => onAddTask(column.id)}
                    className="p-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors shadow-lg"
                    title="Add Task"
                >
                    <Plus size={18} />
                </button>
            </div>

            <div className="flex-1 overflow-y-auto space-y-3 pr-2 custom-scrollbar">
                <SortableContext items={taskIds} strategy={verticalListSortingStrategy}>
                    {filteredTasks.map((task) => (
                        <SortableTaskCard
                            key={task.id}
                            task={task}
                            onEdit={onEditTask}
                            onDelete={onDeleteTask}
                            priorityConfig={priorityConfig}
                        />
                    ))}
                </SortableContext>

                {filteredTasks.length === 0 && tasks.length > 0 && (
                    <div className="text-center py-8 text-slate-500 text-sm">
                        No tasks match your filters
                    </div>
                )}

                {filteredTasks.length === 0 && tasks.length === 0 && (
                    <div className="text-center py-8 text-slate-500 text-sm border-2 border-dashed border-slate-700 rounded-xl">
                        <p className="mb-2">No tasks yet</p>
                        <button
                            onClick={() => onAddTask(column.id)}
                            className="text-blue-400 hover:text-blue-300 text-xs font-medium"
                        >
                            Click to add one
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}

// ============= Main Kanban App =============

function KanbanApp(props = {}) {
    const boardId = props.boardId || 1;

    const [board, setBoard] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [activeTask, setActiveTask] = useState(null);
    const [editModalOpen, setEditModalOpen] = useState(false);
    const [editingTask, setEditingTask] = useState(null);
    const [newTaskColumnId, setNewTaskColumnId] = useState(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [filterPriority, setFilterPriority] = useState('');
    const [draggedTask, setDraggedTask] = useState(null);

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 8,
            },
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    );

    const fetchBoard = useCallback(async (id) => {
        setLoading(true);
        setError(null);
        try {
            const data = await apiRequest(`/api/kanban/boards/${id}`);
            setBoard(data.data);
        } catch (err) {
            console.error(err);
            setError(err.message || 'Failed to load board');
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchBoard(boardId);
    }, [boardId, fetchBoard]);

    const handleDragStart = (event) => {
        const { active } = event;
        const task = findTaskById(active.id);
        setDraggedTask(task);
    };

    const handleDragEnd = async (event) => {
        const { active, over } = event;
        setDraggedTask(null);

        if (!over || !board) return;

        const taskId = active.id;
        const overId = over.id;

        // Check if dropped on a column
        if (typeof overId === 'string' && overId.startsWith('column-')) {
            const targetColumnId = parseInt(overId.replace('column-', ''));
            await moveTaskToColumn(taskId, targetColumnId, 0);
            return;
        }

        // Check if dropped on another task
        const targetTask = findTaskById(overId);
        if (targetTask) {
            const targetColumnId = targetTask.column_id;
            const targetPosition = getTaskPositionInColumn(targetTask);
            await moveTaskToColumn(taskId, targetColumnId, targetPosition);
            return;
        }
    };

    const findTaskById = (taskId) => {
        if (!board?.columns) return null;
        for (const column of board.columns) {
            const task = column.tasks?.find((t) => t.id === taskId);
            if (task) return task;
        }
        return null;
    };

    const getTaskPositionInColumn = (targetTask) => {
        if (!board?.columns) return 0;
        const column = board.columns.find((c) => c.id === targetTask.column_id);
        if (!column?.tasks) return 0;
        return column.tasks.findIndex((t) => t.id === targetTask.id);
    };

    const moveTaskToColumn = async (taskId, columnId, position) => {
        try {
            await apiRequest(`/api/kanban/tasks/${taskId}/move`, {
                method: 'POST',
                body: JSON.stringify({
                    column_id: columnId,
                    position: position,
                }),
            });
            await fetchBoard(boardId);
        } catch (err) {
            console.error('Failed to move task:', err);
            alert(err.message || 'Failed to move task');
        }
    };

    const handleCreateTask = (columnId) => {
        setEditingTask(null);
        setNewTaskColumnId(columnId);
        setEditModalOpen(true);
    };

    const handleEditTask = (task) => {
        setEditingTask(task);
        setEditModalOpen(true);
    };

    const handleDeleteTask = async (taskId) => {
        if (!window.confirm('Delete this task?')) return;

        try {
            await apiRequest(`/api/kanban/tasks/${taskId}`, {
                method: 'DELETE',
            });
            await fetchBoard(boardId);
        } catch (err) {
            alert(err.message || 'Failed to delete task');
        }
    };

    const handleSaveTask = () => {
        fetchBoard(boardId);
    };

    if (loading) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 flex items-center justify-center">
                <div className="text-center">
                    <div className="w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                    <p className="text-slate-400">Loading board…</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 flex items-center justify-center p-4">
                <div className="text-center max-w-md">
                    <AlertCircle className="w-16 h-16 text-red-500 mx-auto mb-4" />
                    <h1 className="text-2xl font-semibold text-white mb-2">Error</h1>
                    <p className="text-slate-400">{error}</p>
                    <button
                        onClick={() => fetchBoard(boardId)}
                        className="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Retry
                    </button>
                </div>
            </div>
        );
    }

    if (!board) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 flex items-center justify-center">
                <p className="text-slate-400">No board loaded.</p>
            </div>
        );
    }

    const columns = board.columns || [];
    const totalTasks = columns.reduce((sum, col) => sum + (col.tasks?.length || 0), 0);

    // Flatten all tasks for drag context
    const allTaskIds = columns.flatMap((col) => col.tasks?.map((t) => t.id) || []);

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragStart={handleDragStart}
            onDragEnd={handleDragEnd}
        >
            <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white">
                {/* Header */}
                <header className="sticky top-0 z-40 bg-slate-900/80 backdrop-blur-lg border-b border-slate-800 shadow-lg">
                    <div className="max-w-[1920px] mx-auto px-6 py-4">
                        <div className="flex items-center justify-between gap-4">
                            <div>
                                <h1 className="text-2xl font-bold text-white">
                                    {board.name}
                                    <span className="ml-2 text-sm font-normal text-slate-400">#{board.id}</span>
                                </h1>
                                <p className="text-sm text-slate-400 mt-1">
                                    {totalTasks} task{totalTasks !== 1 ? 's' : ''} across {columns.length} columns
                                </p>
                            </div>

                            <div className="flex items-center gap-3">
                                {/* Search */}
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400" size={18} />
                                    <input
                                        type="text"
                                        placeholder="Search tasks..."
                                        value={searchQuery}
                                        onChange={(e) => setSearchQuery(e.target.value)}
                                        className="pl-10 pr-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 w-64"
                                    />
                                </div>

                                {/* Priority Filter */}
                                <select
                                    value={filterPriority}
                                    onChange={(e) => setFilterPriority(e.target.value)}
                                    className="px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">All Priorities</option>
                                    {Object.entries(priorityConfig).map(([key, config]) => (
                                        <option key={key} value={key}>
                                            {config.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>
                </header>

                {/* Main Board */}
                <main className="max-w-[1920px] mx-auto px-6 py-6">
                    <div className="flex gap-4 overflow-x-auto pb-4 custom-scrollbar">
                        {columns.map((column) => (
                            <KanbanColumn
                                key={column.id}
                                column={column}
                                tasks={column.tasks || []}
                                onAddTask={handleCreateTask}
                                onEditTask={handleEditTask}
                                onDeleteTask={handleDeleteTask}
                                searchQuery={searchQuery}
                                filterPriority={filterPriority}
                            />
                        ))}
                    </div>
                </main>

                {/* Drag Overlay */}
                <DragOverlay>
                    {draggedTask ? (
                        <div className="w-80">
                            <TaskCard
                                task={draggedTask}
                                onEdit={() => {}}
                                onDelete={() => {}}
                                priorityConfig={priorityConfig}
                            />
                        </div>
                    ) : null}
                </DragOverlay>
            </div>

            {/* Task Edit Modal */}
            <TaskEditModal
                task={editingTask}
                isOpen={editModalOpen}
                onClose={() => {
                    setEditModalOpen(false);
                    setEditingTask(null);
                    setNewTaskColumnId(null);
                }}
                onSave={handleSaveTask}
                boardId={boardId}
                columnId={editingTask?.column_id || newTaskColumnId || null}
                columns={columns}
            />
        </DndContext>
    );
}

// ============= Bootstrap =============

const el = document.getElementById('app');

if (el) {
    let componentName = 'KanbanApp';
    let props = {};

    try {
        if (el.dataset.component) {
            componentName = JSON.parse(el.dataset.component);
        }
        if (el.dataset.props) {
            props = JSON.parse(el.dataset.props);
        }
    } catch (e) {
        console.error('Failed to parse SPA dataset:', e);
    }

    const components = {
        KanbanApp,
    };

    const Component = components[componentName] || KanbanApp;

    ReactDOM.createRoot(el).render(
        <React.StrictMode>
            <Component {...props} />
        </React.StrictMode>
    );
}

export default KanbanApp;
