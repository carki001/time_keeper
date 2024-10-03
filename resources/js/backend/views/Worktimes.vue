<template>
    <div>
        <header>
            <v-container>
                <v-row no-gutters>
                    <h1 class="font-weight-light headline">
                        {{ $t("title.worktimes") }}
                    </h1>
                    <v-spacer></v-spacer>
                    <v-lazy
                        v-if="isWorktimesLoaded"
                        transition="fade-transition"
                    >
                        <v-text-field
                            v-model="search"
                            :append-icon="!search ? 'mdi-magnify' : ''"
                            :label="searchLabel"
                            single-line
                            dense
                            hide-details
                            style="max-width: 200px"
                            class="mr-5 pr-5"
                            :class="
                                search == null || search.length < 3
                                    ? 'itb-input-text'
                                    : ''
                            "
                            :color="
                                search == null || search.length < 3
                                    ? 'warning'
                                    : ''
                            "
                            @input="searchTasks"
                            @keyup.delete="searchTaskByDelete"
                        ></v-text-field>
                    </v-lazy>
                    <v-lazy
                        v-if="isWorktimesLoaded"
                        transition="fade-transition"
                    >
                        <v-btn
                            color="success"
                            small
                            @click="prepareWorkTime"
                            :dark="!(timerOn || isNewWorktimeReady)"
                            :disabled="timerOn || isNewWorktimeReady"
                            >{{ $t("general.add") }}</v-btn
                        >
                    </v-lazy>
                </v-row>
            </v-container>
        </header>
        <section>
            <v-container class="pt-0">
                <v-sheet class="px-3 pt-3 pb-3" v-if="!isWorktimesLoaded">
                    <v-skeleton-loader
                        class="mx-auto"
                        type="table"
                    ></v-skeleton-loader>
                </v-sheet>
                <v-card v-else>
                    <v-data-iterator
                        :items="tasks"
                        :items-per-page="itemsPerPage"
                        :page="page"
                        :footer-props="{
                            'items-per-page-options': itemsPerPageOptions,
                        }"
                        :server-items-length="tasksCount"
                        @pagination="getWorktimesByPage"
                    >
                        <template v-if="!isMobile" v-slot:header>
                            <v-toolbar
                                :height="
                                    $vuetify.breakpoint.mobile ? '48' : '32'
                                "
                                flat
                            >
                                <v-row class="ms-0 me-0">
                                    <v-col
                                        v-for="(header, headerIndex) in headers"
                                        :key="headerIndex"
                                        class="py-0"
                                        :class="{
                                            'ps-4 pe-2': headerIndex == 0,
                                        }"
                                    >
                                        <span class="itb-tasks-titles">
                                            {{ header.text }}
                                        </span>
                                        <v-spacer></v-spacer>
                                    </v-col>
                                </v-row>
                                <div style="width: 48px"></div>
                            </v-toolbar>
                        </template>
                        <template v-slot:default="props">
                            <v-form
                                v-if="isNewWorktimeReady"
                                ref="form"
                                v-model="valid"
                                class="d-flex green lighten-4"
                            >
                                <v-row class="ms-0 me-0">
                                    <v-col
                                        class="d-flex align-center"
                                        :cols="isMobile ? '12' : ''"
                                    >
                                        <v-tooltip top>
                                            <template v-slot:activator="{ on }">
                                                <v-icon
                                                    size="20px"
                                                    color="success"
                                                    v-on="on"
                                                    @click="startTimer"
                                                    >mdi-play
                                                </v-icon>
                                            </template>
                                            <span>{{
                                                $t("worktimes.start")
                                            }}</span>
                                        </v-tooltip>
                                        <v-tooltip v-if="!timerOn" top>
                                            <template v-slot:activator="{ on }">
                                                <v-icon
                                                    color="red"
                                                    v-on="on"
                                                    @click="
                                                        isNewWorktimeReady = false
                                                    "
                                                    class="ms-5"
                                                >
                                                    mdi-close
                                                </v-icon>
                                            </template>
                                            <span>{{
                                                $t("general.cancel")
                                            }}</span>
                                        </v-tooltip>
                                    </v-col>
                                    <v-col :cols="isMobile ? '12' : ''">
                                        <v-combobox
                                            :disabled="timerOn"
                                            :items="similarTasks"
                                            :search-input.sync="taskNameSearch"
                                            :rules="taskNameRule"
                                            :class="
                                                !!taskNameSearch &&
                                                taskNameSearch.length < 3
                                                    ? 'itb-input-text'
                                                    : ''
                                            "
                                            :color="
                                                !!taskNameSearch &&
                                                taskNameSearch.length < 3
                                                    ? 'warning'
                                                    : ''
                                            "
                                            :multiple="false"
                                            :chips="false"
                                            :return-object="false"
                                            v-model="newTask.name"
                                            item-value="name"
                                            item-text="name"
                                            class="py-0 itb-new-task-field mt-0"
                                            hide-no-data
                                            autofocus
                                            @update:search-input="
                                                searchForNewTask
                                            "
                                            @change="setProjectSuggestion"
                                            @keyup.delete="
                                                searchForNewTaskByDelete('task')
                                            "
                                        >
                                        </v-combobox>
                                    </v-col>
                                    <v-col :cols="isMobile ? '12' : ''">
                                        <v-autocomplete
                                            :disabled="timerOn"
                                            :items="projects"
                                            class="py-0 itb-new-task-field mt-0"
                                            v-model="newTask.project_id"
                                            item-value="id"
                                            item-text="name"
                                            :rules="requiredRule"
                                        ></v-autocomplete>
                                    </v-col>
                                    <v-col v-if="!isMobile"></v-col>
                                    <v-col v-if="!isMobile"></v-col>
                                    <v-col v-if="!isMobile"></v-col>
                                    <v-col
                                        v-if="!isMobile"
                                        style="max-width: 50px; cursor: pointer"
                                        class="ps-0"
                                    >
                                    </v-col>
                                </v-row>
                            </v-form>
                            <v-expansion-panels focusable flat>
                                <v-expansion-panel v-if="timerOn">
                                    <v-expansion-panel-header
                                        class="py-0 green lighten-4"
                                        hide-actions
                                        :class="{
                                            'itb-task-row-no-mobile': !isMobile,
                                        }"
                                    >
                                        <v-row
                                            class="ms-0 me-0"
                                            :class="{ 'py-4': isMobile }"
                                            style="padding-right: 24px"
                                        >
                                            <v-col
                                                :cols="isMobile ? '12' : ''"
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                }"
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold itb-tasks-text"
                                                    >{{
                                                        headersObj.action
                                                    }}</span
                                                >
                                                <div>
                                                    <v-tooltip top>
                                                        <template
                                                            v-slot:activator="{
                                                                on,
                                                            }"
                                                        >
                                                            <v-icon
                                                                size="20px"
                                                                color="red"
                                                                v-on="on"
                                                                @click="
                                                                    stopTimer(
                                                                        false,
                                                                        false
                                                                    )
                                                                "
                                                                >mdi-stop
                                                            </v-icon>
                                                        </template>
                                                        <span>{{
                                                            $t("worktimes.stop")
                                                        }}</span>
                                                    </v-tooltip>
                                                    <v-tooltip top>
                                                        <template
                                                            v-slot:activator="{
                                                                on,
                                                            }"
                                                        >
                                                            <v-icon
                                                                size="20px"
                                                                color="red"
                                                                class="ms-4"
                                                                @click="
                                                                    confirmDeleteTask(
                                                                        null,
                                                                        null
                                                                    )
                                                                "
                                                                v-on="on"
                                                                >mdi-delete-outline</v-icon
                                                            >
                                                        </template>
                                                        <span>{{
                                                            $t("general.delete")
                                                        }}</span>
                                                    </v-tooltip>
                                                </div>
                                            </v-col>
                                            <v-col
                                                class="itb-tasks-text"
                                                :cols="isMobile ? '12' : ''"
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                }"
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold"
                                                    >{{ headersObj.name }}
                                                </span>

                                                <v-tooltip
                                                    v-if="
                                                        newTask.name.length > 28
                                                    "
                                                    top
                                                >
                                                    <template
                                                        v-slot:activator="{
                                                            on,
                                                        }"
                                                    >
                                                        <span v-on="on">
                                                            {{
                                                                newTask.name
                                                                    .length > 28
                                                                    ? newTask.name.slice(
                                                                          0,
                                                                          24
                                                                      ) + "..."
                                                                    : newTask.name
                                                            }}
                                                        </span>
                                                    </template>
                                                    <span>{{
                                                        newTask.name
                                                    }}</span>
                                                </v-tooltip>
                                                <span v-else>{{
                                                    newTask.name
                                                }}</span>
                                            </v-col>
                                            <v-col
                                                class="itb-tasks-text"
                                                :cols="isMobile ? '12' : ''"
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                }"
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold"
                                                    >{{
                                                        headersObj.project_name
                                                    }}
                                                </span>
                                                <span>
                                                    {{
                                                        projects.find(
                                                            (project) =>
                                                                project.id ==
                                                                newTask.project_id
                                                        ).name
                                                    }}
                                                </span>
                                            </v-col>
                                            <v-col
                                                v-if="!isMobile"
                                                class="itb-tasks-text"
                                                :cols="isMobile ? '12' : ''"
                                                >-</v-col
                                            >
                                            <v-col
                                                class="itb-tasks-text"
                                                :cols="isMobile ? '12' : ''"
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                }"
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold"
                                                    >{{ headersObj.duration }}
                                                </span>
                                                <span>
                                                    {{ currentTime }}
                                                </span>
                                            </v-col>
                                            <v-col
                                                v-if="!isMobile"
                                                class="itb-tasks-text"
                                                :cols="isMobile ? '12' : ''"
                                                >&nbsp;-</v-col
                                            >
                                        </v-row>
                                    </v-expansion-panel-header>
                                </v-expansion-panel>
                                <v-expansion-panel
                                    v-for="(task, taskIndex) in props.items"
                                    :key="task.id"
                                    :class="{
                                        'green lighten-4':
                                            taskIndex == 0 && isTaskCreated,
                                    }"
                                >
                                    <v-expansion-panel-header
                                        class="py-0"
                                        :class="{
                                            'itb-task-row-no-mobile': !isMobile,
                                        }"
                                    >
                                        <v-row
                                            class="ms-0 me-0"
                                            :class="{ 'py-4': isMobile }"
                                        >
                                            <v-col
                                                :cols="isMobile ? '12' : ''"
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                }"
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold itb-tasks-text"
                                                    >{{
                                                        headersObj.action
                                                    }}</span
                                                >
                                                <div>
                                                    <v-tooltip
                                                        v-if="
                                                            !task.isRunning &&
                                                            !task.withoutTasks
                                                        "
                                                        top
                                                    >
                                                        <template
                                                            v-slot:activator="{
                                                                on,
                                                            }"
                                                        >
                                                            <v-icon
                                                                size="20px"
                                                                color="success"
                                                                class="mr-2"
                                                                v-on="on"
                                                                @click="
                                                                    startTimerExistingTask(
                                                                        task,
                                                                        taskIndex
                                                                    )
                                                                "
                                                                >mdi-play
                                                            </v-icon>
                                                        </template>
                                                        <span>{{
                                                            $t(
                                                                "worktimes.start"
                                                            )
                                                        }}</span>
                                                    </v-tooltip>
                                                    <v-tooltip
                                                        v-if="task.isRunning"
                                                        top
                                                    >
                                                        <template
                                                            v-slot:activator="{
                                                                on,
                                                            }"
                                                        >
                                                            <v-icon
                                                                size="20px"
                                                                color="red"
                                                                class="mr-2"
                                                                v-on="on"
                                                                @click="
                                                                    stopTimer(
                                                                        false,
                                                                        false
                                                                    )
                                                                "
                                                                >mdi-stop
                                                            </v-icon>
                                                        </template>
                                                        <span>{{
                                                            $t("worktimes.stop")
                                                        }}</span>
                                                    </v-tooltip>
                                                    <v-tooltip
                                                        v-if="
                                                            !task.withoutTasks
                                                        "
                                                        top
                                                    >
                                                        <template
                                                            v-slot:activator="{
                                                                on,
                                                            }"
                                                        >
                                                            <v-icon
                                                                size="20px"
                                                                color="red"
                                                                class="ms-2"
                                                                @click="
                                                                    confirmDeleteTask(
                                                                        task,
                                                                        taskIndex
                                                                    )
                                                                "
                                                                v-on="on"
                                                                >mdi-delete-outline</v-icon
                                                            >
                                                        </template>
                                                        <span>{{
                                                            $t("general.delete")
                                                        }}</span>
                                                    </v-tooltip>
                                                </div>
                                            </v-col>
                                            <v-col
                                                class="itb-tasks-text"
                                                :cols="isMobile ? '12' : ''"
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                }"
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold"
                                                    >{{ headersObj.name }}
                                                </span>
                                                <v-tooltip
                                                    v-if="task.name.length > 28"
                                                    top
                                                >
                                                    <template
                                                        v-slot:activator="{
                                                            on,
                                                        }"
                                                    >
                                                        <span v-on="on">
                                                            {{
                                                                task.name
                                                                    .length > 28
                                                                    ? task.name.slice(
                                                                          0,
                                                                          24
                                                                      ) + "..."
                                                                    : task.name
                                                            }}
                                                        </span>
                                                    </template>
                                                    <span>{{ task.name }}</span>
                                                </v-tooltip>
                                                <span v-else>{{
                                                    task.name
                                                }}</span>
                                            </v-col>
                                            <v-col
                                                class="itb-tasks-text"
                                                :cols="isMobile ? '12' : ''"
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                }"
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold"
                                                    >{{
                                                        headersObj.project_name
                                                    }}
                                                </span>
                                                <span>
                                                    {{ task.project_name }}
                                                </span>
                                            </v-col>
                                            <v-col
                                                class="itb-tasks-text"
                                                :cols="isMobile ? '12' : ''"
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                }"
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold"
                                                    >{{
                                                        headersObj.worktimes_count
                                                    }}
                                                </span>
                                                <span>
                                                    {{ task.worktimes_count }}
                                                </span>
                                            </v-col>
                                            <v-col
                                                class="itb-tasks-text"
                                                :cols="isMobile ? '12' : ''"
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                }"
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold"
                                                    >{{ headersObj.duration }}
                                                </span>
                                                <span>
                                                    {{
                                                        task.timeInformation
                                                            .formattedDuration
                                                    }}
                                                </span>
                                            </v-col>
                                            <v-col
                                                class="itb-tasks-text"
                                                :cols="isMobile ? '12' : ''"
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                }"
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold"
                                                    >{{
                                                        headersObj.formattedDate
                                                    }}
                                                </span>
                                                <span>
                                                    {{ task.formattedDate }}
                                                </span>
                                            </v-col>
                                        </v-row>
                                    </v-expansion-panel-header>
                                    <v-expansion-panel-content>
                                        <v-row
                                            v-for="(
                                                worktime, worktimeIndex
                                            ) in task.worktimes"
                                            :key="worktime.id"
                                            :class="{
                                                'pe-9 itb-worktime-row-mobile':
                                                    isMobile,
                                                'itb-worktime-row-no-mobile':
                                                    !isMobile &&
                                                    !worktime.is_editing,
                                            }"
                                        >
                                            <v-col
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                    'd-flex align-center':
                                                        !isMobile,
                                                }"
                                                :cols="
                                                    setEditionColumnWidth(
                                                        worktime.is_editing
                                                    )
                                                "
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold itb-tasks-text"
                                                    >{{ headersObj.action }}
                                                </span>
                                                <div>
                                                    <v-tooltip top>
                                                        <template
                                                            v-slot:activator="{
                                                                on,
                                                            }"
                                                        >
                                                            <v-icon
                                                                v-show="
                                                                    !worktime.is_editing
                                                                "
                                                                size="20px"
                                                                color="blue"
                                                                class="mr-2"
                                                                @click="
                                                                    editWorktime(
                                                                        taskIndex,
                                                                        worktimeIndex
                                                                    )
                                                                "
                                                                v-on="on"
                                                                >mdi-pencil-outline</v-icon
                                                            >
                                                        </template>
                                                        <span>{{
                                                            $t("general.edit")
                                                        }}</span>
                                                    </v-tooltip>
                                                    <v-tooltip top>
                                                        <template
                                                            v-slot:activator="{
                                                                on,
                                                            }"
                                                        >
                                                            <v-icon
                                                                v-show="
                                                                    worktime.is_editing
                                                                "
                                                                size="20px"
                                                                color="blue"
                                                                class="mr-2"
                                                                @click="
                                                                    saveWorktime(
                                                                        taskIndex,
                                                                        worktimeIndex
                                                                    )
                                                                "
                                                                v-on="on"
                                                                >mdi-content-save-outline</v-icon
                                                            >
                                                        </template>
                                                        <span>{{
                                                            $t("general.save")
                                                        }}</span>
                                                    </v-tooltip>
                                                    <v-tooltip top>
                                                        <template
                                                            v-slot:activator="{
                                                                on,
                                                            }"
                                                        >
                                                            <v-icon
                                                                size="20px"
                                                                color="red"
                                                                class="ms-2"
                                                                @click="
                                                                    confirmDeleteWorktime(
                                                                        taskIndex,
                                                                        worktimeIndex
                                                                    )
                                                                "
                                                                v-on="on"
                                                                >mdi-delete-outline</v-icon
                                                            >
                                                        </template>
                                                        <span>{{
                                                            $t("general.delete")
                                                        }}</span>
                                                    </v-tooltip>
                                                    <v-tooltip
                                                        v-if="
                                                            worktime.is_editing
                                                        "
                                                        top
                                                    >
                                                        <template
                                                            v-slot:activator="{
                                                                on,
                                                            }"
                                                        >
                                                            <v-icon
                                                                color="red"
                                                                v-on="on"
                                                                @click="
                                                                    cancelWorktimeEdition(
                                                                        taskIndex,
                                                                        worktimeIndex
                                                                    )
                                                                "
                                                                class="ms-5"
                                                            >
                                                                mdi-close
                                                            </v-icon>
                                                        </template>
                                                        <span>{{
                                                            $t("general.cancel")
                                                        }}</span>
                                                    </v-tooltip>
                                                </div>
                                            </v-col>
                                            <v-col
                                                v-if="!worktime.is_editing"
                                                class="itb-tasks-text"
                                                :cols="isMobile ? '12' : ''"
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                }"
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold"
                                                    >{{ headersObj.name }}
                                                </span>
                                                <v-tooltip
                                                    v-if="
                                                        worktime.task_name
                                                            .length > 28
                                                    "
                                                    top
                                                >
                                                    <template
                                                        v-slot:activator="{
                                                            on,
                                                        }"
                                                    >
                                                        <span v-on="on">
                                                            {{
                                                                worktime
                                                                    .task_name
                                                                    .length > 28
                                                                    ? worktime.task_name.slice(
                                                                          0,
                                                                          24
                                                                      ) + "..."
                                                                    : worktime.task_name
                                                            }}
                                                        </span>
                                                    </template>
                                                    <span>{{
                                                        worktime.task_name
                                                    }}</span>
                                                </v-tooltip>
                                                <span v-else>{{
                                                    worktime.task_name
                                                }}</span>
                                            </v-col>
                                            <v-col
                                                v-if="!worktime.is_editing"
                                                class="itb-tasks-text"
                                                :cols="isMobile ? '12' : ''"
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                }"
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold"
                                                    >{{
                                                        headersObj.project_name
                                                    }}
                                                </span>
                                                <span>
                                                    {{
                                                        projects.find(
                                                            (project) =>
                                                                project.id ==
                                                                worktime.project_id
                                                        ).name
                                                    }}
                                                </span>
                                            </v-col>
                                            <v-col
                                                v-if="
                                                    !(
                                                        worktime.is_editing ||
                                                        isMobile
                                                    )
                                                "
                                                class="itb-tasks-text"
                                                :cols="isMobile ? '12' : ''"
                                                >-</v-col
                                            >
                                            <v-col
                                                v-if="!worktime.is_editing"
                                                class="itb-worktimes-text"
                                                :cols="isMobile ? '12' : ''"
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                }"
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold"
                                                    >{{ headersObj.duration }}
                                                </span>
                                                <span>
                                                    {{
                                                        worktime.formattedDuration
                                                    }}
                                                </span>
                                            </v-col>
                                            <v-col
                                                v-if="!worktime.is_editing"
                                                class="itb-worktimes-text"
                                                :cols="isMobile ? '12' : ''"
                                                :class="{
                                                    'd-flex justify-space-between':
                                                        isMobile,
                                                }"
                                            >
                                                <span
                                                    v-if="isMobile"
                                                    class="font-weight-bold"
                                                    >{{
                                                        headersObj.formattedDate
                                                    }}
                                                </span>
                                                <span>
                                                    {{
                                                        worktime.started_at_formatted
                                                    }}
                                                </span>
                                            </v-col>
                                            <v-col
                                                v-if="!worktime.is_editing"
                                                style="max-width: 36px"
                                                :cols="isMobile ? '12' : ''"
                                            ></v-col>
                                            <v-col v-if="worktime.is_editing">
                                                <v-form
                                                    :ref="
                                                        'formWorktime' +
                                                        '-' +
                                                        taskIndex +
                                                        '-' +
                                                        worktimeIndex
                                                    "
                                                    v-model="validWorktime"
                                                    class="d-flex"
                                                >
                                                    <v-row>
                                                        <v-col
                                                            class="itb-worktime-edition"
                                                            :cols="
                                                                isMobile
                                                                    ? '12'
                                                                    : ''
                                                            "
                                                        >
                                                            <v-combobox
                                                                :items="
                                                                    similarTasksWorktime
                                                                "
                                                                :search-input.sync="
                                                                    taskNameSearchWorktime[
                                                                        taskIndex +
                                                                            '-' +
                                                                            worktimeIndex
                                                                    ]
                                                                "
                                                                :rules="
                                                                    taskNameRule
                                                                "
                                                                :class="
                                                                    !!taskNameSearchWorktime[
                                                                        taskIndex +
                                                                            '-' +
                                                                            worktimeIndex
                                                                    ] &&
                                                                    taskNameSearchWorktime[
                                                                        taskIndex +
                                                                            '-' +
                                                                            worktimeIndex
                                                                    ].length < 3
                                                                        ? 'itb-input-text'
                                                                        : ''
                                                                "
                                                                :color="
                                                                    !!taskNameSearchWorktime[
                                                                        taskIndex +
                                                                            '-' +
                                                                            worktimeIndex
                                                                    ] &&
                                                                    taskNameSearchWorktime[
                                                                        taskIndex +
                                                                            '-' +
                                                                            worktimeIndex
                                                                    ].length < 3
                                                                        ? 'warning'
                                                                        : ''
                                                                "
                                                                :multiple="
                                                                    false
                                                                "
                                                                :chips="false"
                                                                :return-object="
                                                                    false
                                                                "
                                                                v-model="
                                                                    worktime.task_name
                                                                "
                                                                item-value="name"
                                                                item-text="name"
                                                                class="py-0 itb-edit-task-field"
                                                                hide-no-data
                                                                autofocus
                                                                @update:search-input="
                                                                    searchForNewTaskWorktime(
                                                                        taskIndex,
                                                                        worktimeIndex
                                                                    )
                                                                "
                                                                @click="
                                                                    searchForNewTaskWorktime(
                                                                        taskIndex,
                                                                        worktimeIndex
                                                                    )
                                                                "
                                                                @keyup.delete="
                                                                    searchForNewTaskByDelete(
                                                                        'worktime',
                                                                        taskIndex,
                                                                        worktimeIndex
                                                                    )
                                                                "
                                                            >
                                                            </v-combobox>
                                                        </v-col>
                                                        <v-col
                                                            :cols="
                                                                isMobile
                                                                    ? '12'
                                                                    : ''
                                                            "
                                                        >
                                                            <v-autocomplete
                                                                :items="
                                                                    projects
                                                                "
                                                                class="py-0 itb-edit-task-field"
                                                                v-model="
                                                                    worktime.project_id
                                                                "
                                                                item-value="id"
                                                                item-text="name"
                                                                :rules="
                                                                    requiredRule
                                                                "
                                                            ></v-autocomplete>
                                                        </v-col>
                                                        <v-col
                                                            :cols="
                                                                isMobile
                                                                    ? '12'
                                                                    : ''
                                                            "
                                                        >
                                                            <v-menu
                                                                :ref="
                                                                    'menuDuration' +
                                                                    '-' +
                                                                    taskIndex +
                                                                    '-' +
                                                                    worktimeIndex
                                                                "
                                                                v-model="
                                                                    worktime.is_duration_picker_visible
                                                                "
                                                                :close-on-content-click="
                                                                    false
                                                                "
                                                                :nudge-right="
                                                                    40
                                                                "
                                                                :return-value.sync="
                                                                    worktime.formattedDuration
                                                                "
                                                                transition="scale-transition"
                                                                offset-y
                                                                max-width="290px"
                                                                min-width="290px"
                                                            >
                                                                <template
                                                                    v-slot:activator="{
                                                                        on,
                                                                        attrs,
                                                                    }"
                                                                >
                                                                    <v-text-field
                                                                        v-model="
                                                                            worktime.formattedDuration
                                                                        "
                                                                        :label="
                                                                            $t(
                                                                                'worktimes.duration'
                                                                            )
                                                                        "
                                                                        prepend-icon="mdi-clock-time-four-outline"
                                                                        readonly
                                                                        v-bind="
                                                                            attrs
                                                                        "
                                                                        v-on="
                                                                            on
                                                                        "
                                                                        :rules="
                                                                            durationRule
                                                                        "
                                                                    ></v-text-field>
                                                                </template>
                                                                <v-time-picker
                                                                    format="24hr"
                                                                    use-seconds
                                                                    v-if="
                                                                        worktime.is_duration_picker_visible
                                                                    "
                                                                    v-model="
                                                                        worktime.formattedDuration
                                                                    "
                                                                    full-width
                                                                    @click:second="
                                                                        $refs[
                                                                            'menuDuration' +
                                                                                '-' +
                                                                                taskIndex +
                                                                                '-' +
                                                                                worktimeIndex
                                                                        ][0].save(
                                                                            worktime.formattedDuration
                                                                        )
                                                                    "
                                                                ></v-time-picker>
                                                            </v-menu>
                                                        </v-col>
                                                        <v-col
                                                            :cols="
                                                                isMobile
                                                                    ? '12'
                                                                    : ''
                                                            "
                                                        >
                                                            <v-menu
                                                                v-model="
                                                                    worktime.is_started_date_picker_visible
                                                                "
                                                                :close-on-content-click="
                                                                    false
                                                                "
                                                                :nudge-right="
                                                                    40
                                                                "
                                                                transition="scale-transition"
                                                                offset-y
                                                                min-width="auto"
                                                            >
                                                                <template
                                                                    v-slot:activator="{
                                                                        on,
                                                                        attrs,
                                                                    }"
                                                                >
                                                                    <v-text-field
                                                                        v-model="
                                                                            worktime.started_at_short_formatted
                                                                        "
                                                                        :label="
                                                                            $t(
                                                                                'worktimes.started_at_date'
                                                                            )
                                                                        "
                                                                        prepend-icon="mdi-calendar"
                                                                        readonly
                                                                        v-bind="
                                                                            attrs
                                                                        "
                                                                        v-on="
                                                                            on
                                                                        "
                                                                    ></v-text-field>
                                                                </template>
                                                                <v-date-picker
                                                                    :locale="
                                                                        datePickerLocale
                                                                    "
                                                                    v-model="
                                                                        worktime.started_at_short_standard
                                                                    "
                                                                    @input="
                                                                        prepareWorkTimeDate(
                                                                            taskIndex,
                                                                            worktimeIndex
                                                                        )
                                                                    "
                                                                ></v-date-picker>
                                                            </v-menu>
                                                        </v-col>
                                                        <v-col
                                                            :cols="
                                                                isMobile
                                                                    ? '12'
                                                                    : ''
                                                            "
                                                        >
                                                            <v-menu
                                                                :ref="
                                                                    'menuStartedAtTime' +
                                                                    '-' +
                                                                    taskIndex +
                                                                    '-' +
                                                                    worktimeIndex
                                                                "
                                                                v-model="
                                                                    worktime.is_started_at_time_picker_visible
                                                                "
                                                                :close-on-content-click="
                                                                    false
                                                                "
                                                                :nudge-right="
                                                                    40
                                                                "
                                                                :return-value.sync="
                                                                    worktime.started_at_time_formatted
                                                                "
                                                                transition="scale-transition"
                                                                offset-y
                                                                max-width="290px"
                                                                min-width="290px"
                                                            >
                                                                <template
                                                                    v-slot:activator="{
                                                                        on,
                                                                        attrs,
                                                                    }"
                                                                >
                                                                    <v-text-field
                                                                        v-model="
                                                                            worktime.started_at_time_formatted
                                                                        "
                                                                        :label="
                                                                            $t(
                                                                                'worktimes.started_at_time'
                                                                            )
                                                                        "
                                                                        prepend-icon="mdi-clock-time-four-outline"
                                                                        readonly
                                                                        v-bind="
                                                                            attrs
                                                                        "
                                                                        v-on="
                                                                            on
                                                                        "
                                                                    ></v-text-field>
                                                                </template>
                                                                <v-time-picker
                                                                    format="24hr"
                                                                    use-seconds
                                                                    v-if="
                                                                        worktime.is_started_at_time_picker_visible
                                                                    "
                                                                    v-model="
                                                                        worktime.started_at_time_formatted
                                                                    "
                                                                    full-width
                                                                    @click:second="
                                                                        $refs[
                                                                            'menuStartedAtTime' +
                                                                                '-' +
                                                                                taskIndex +
                                                                                '-' +
                                                                                worktimeIndex
                                                                        ][0].save(
                                                                            worktime.started_at_time_formatted
                                                                        )
                                                                    "
                                                                ></v-time-picker>
                                                            </v-menu>
                                                        </v-col>
                                                    </v-row>
                                                </v-form>
                                            </v-col>
                                        </v-row>
                                    </v-expansion-panel-content>
                                </v-expansion-panel>
                            </v-expansion-panels>
                        </template>
                    </v-data-iterator>
                </v-card>

                <ConfirmationDialog
                    :name="dialog.confirmationTaskName"
                    :text="confirmationText.task"
                    v-if="dialog.confirmationTask"
                    @confirm="deleteTask"
                    @cancel="cancelDelete"
                />

                <ConfirmationDialog
                    :name="dialog.confirmationWorktimeName"
                    :text="confirmationText.worktime"
                    v-if="dialog.confirmationWorktime"
                    @confirm="deleteWorktime"
                    @cancel="cancelDeleteWorktime"
                />

                <SnackMessage ref="SnackMessage" />
            </v-container>
        </section>
    </div>
</template>

<script>
import ConfirmationDialog from "../components/ConfirmationDialog";
import SnackMessage from "../components/SnackMessage";
import { defaultLanguage, tagsBCP47 } from "../../i18n";

export default {
    data() {
        return {
            valid: true,
            validWorktime: true,
            tasks: [],
            projects: [],
            isWorktimesLoaded: false,
            search: "",
            dialog: {
                confirmationTask: false,
                confirmationWorktime: false,
                confirmationTaskName: "",
                confirmationWorktimeName: "",
            },

            selectedWorktime: {
                name: null,
                team_id: null,
                is_active: false,
            },
            defaultWorktime: {
                name: null,
                team_id: null,
                is_active: false,
            },
            isWorktimeEdited: false,
            user: null,
            newTask: {
                name: "",
                is_active: 1,
                project_id: null,

                user_id: null,
                task_id: null,
                started_at: "",
                finished_at: "",
            },
            defaultNewTask: {
                name: "",
                is_active: 1,
                project_id: null,

                user_id: null,
                task_id: null,
                started_at: "",
                finished_at: "",
            },
            requiredRule: [(value) => !!value || this.$t("general.required")],
            durationRule: [
                (value) =>
                    value != "00:00:00" ||
                    this.$t("general.value_greater_than_zero"),
            ],
            taskNameRule: [
                (value) => !!value || this.$t("general.required"),
                (value) =>
                    value == null ||
                    value.length <= 100 ||
                    this.$t("general.max_length_task_name"),
            ],
            currentTime: "00:00:00",
            seconds: 0,
            minutes: 0,
            hours: 0,
            totalSeconds: 0,
            timeInterval: null,
            timerOn: false,
            createdTask: {},
            createdWorktime: {},
            formattedDate: "",
            ongoingWorktime: {},
            isNewWorktimeReady: false,
            itemsPerPage: 20,
            itemsPerPageOptions: [20, 40, 60, -1],
            page: 1,
            runningTaskPosition: null,
            currentTimezone: null,
            timezones: [],
            sessionT: null,
            confirmationText: {
                task: "worktimes.delete_task_confirmation",
                worktime: "worktimes.delete_confirmation",
            },
            taskToDelete: null,
            worktimeToDelete: null,
            taskIndexToDelete: null,
            worktimeIndexesToDelete: { task: null, worktime: null },
            isPageLoaded: false,
            paginationState: {},
            tasksCount: 0,
            requestWorktimesCounter: 0,
            isSearching: true,
            withoutTasks: false,
            similarTasks: [],
            taskNameSearch: null,
            isSearchingForNewTask: false,
            similarTasksWorktime: [],
            taskNameSearchWorktime: {},
            isSearchingForNewTaskWorktime: false,
            defaultLanguage: defaultLanguage,
            tagsBCP47: tagsBCP47,
            isTaskCreated: false,
            isMobile: false,
            suggestedProject: {},
        };
    },

    components: {
        ConfirmationDialog,
        SnackMessage,
    },

    computed: {
        headers() {
            return [
                {
                    text: this.$t("general.actions"),
                    value: "action",
                    sortable: false,
                },
                {
                    text: this.$t("tasks.name"),
                    align: "left",
                    sortable: false,
                    value: "name",
                },
                {
                    text: this.$t("tasks.project_name"),
                    align: "left",
                    sortable: false,
                    value: "project_name",
                },
                {
                    text: this.$t("tasks.worktimes_count"),
                    align: "left",
                    sortable: false,
                    value: "worktimes_count",
                },
                {
                    text: this.$t("tasks.duration"),
                    align: "left",
                    sortable: false,
                    value: "duration",
                },

                {
                    text: this.$t("tasks.last_date_worked_on"),
                    align: "left",
                    sortable: false,
                    value: "formattedDate",
                },
            ];
        },
        headersObj() {
            var headersObj = {};
            this.headers.forEach((header) => {
                headersObj[header.value] = header.text;
            });
            return headersObj;
        },
        searchLabel() {
            return this.$t("general.search");
        },
        datePickerLocale() {
            var language;
            if (!this.$store.state.profile.preferred_language) {
                language = defaultLanguage;
            } else {
                language = this.$store.state.profile.preferred_language;
            }
            return this.tagsBCP47[language];
        },
    },

    created() {
        this.getWorktimes();

        this.onResize();
        window.addEventListener("resize", this.onResize);
    },

    mounted() {
        this.refreshTimer();
        document.addEventListener("visibilitychange", this.refreshTimer);
        this.isPageLoaded = true;

        this.$eventBus.$on("refreshCurrentUser", (data) => {
            this.getWorktimes(true);
        });
    },

    beforeDestroy() {
        document.removeEventListener("visibilitychange", this.refreshTimer);

        window.removeEventListener("resize", this.onResize);
    },

    methods: {
        snackMessage(msg, type, isCustomMessage = false) {
            this.$refs.SnackMessage.showMessage(msg, type, isCustomMessage);
        },

        async getWorktimes(isChangingPage = false) {
            this.user = this.$store.state.profile;
            this.newTask.user_id = this.user.id;
            this.defaultNewTask.user_id = this.user.id;
            await axios
                .post(
                    "/api/worktimeList",
                    {
                        user_id: this.user.id,
                        page: this.page,
                        items_per_page: this.itemsPerPage,
                        search: this.search,
                    },
                    {
                        headers: {
                            Authorization:
                                "Bearer " +
                                this.$store.state.tokenData.user.access_token,
                        },
                    }
                )
                .then((response) => {
                    this.tasksCount = response.data.tasksCount;
                    this.currentTimezone = response.data.currentTimezone;
                    this.tasks = response.data.tasks ? response.data.tasks : [];
                    this.projects = response.data.projects
                        ? response.data.projects
                        : [];
                    this.ongoingWorktime = response.data.ongoingWorktime
                        ? response.data.ongoingWorktime
                        : null;
                    this.timezones = response.data.timezones
                        ? response.data.timezones
                        : [];
                    this.isWorktimesLoaded = true;

                    setTimeout(() => {
                        this.isSearching = false;
                    }, 300);

                    this.sessionT = response.data.sessionT;
                    if (!!this.ongoingWorktime && !isChangingPage) {
                        this.updateOngoingTime();
                    }
                    this.withoutTasks = response.data.withoutTasks;
                })
                .catch((error) => {
                    console.log(error);
                    this.$refs.SnackMessage.showMessage(
                        "general.error",
                        "error"
                    );
                });
        },
        updateOngoingTime() {
            this.newTask.name = this.ongoingWorktime.task.name;
            this.newTask.project_id = this.ongoingWorktime.project.id;
            if (this.ongoingWorktime.runningTaskPosition) {
                this.runningTaskPosition =
                    this.ongoingWorktime.runningTaskPosition;
            }
            this.createdWorktime = JSON.parse(
                JSON.stringify(this.ongoingWorktime)
            );
            let startedAtDate = this.ongoingWorktime.started_at.split(" ")[0];
            let startedAtTime = this.ongoingWorktime.started_at.split(" ")[1];
            let startedAtMiliseconds = Date.parse(
                startedAtDate + "T" + startedAtTime + ".000" + "Z"
            );
            let timePased = Date.now() - startedAtMiliseconds;
            let timePasedByHours = timePased / 1000 / 60 / 60;
            let hoursPased = Math.floor(timePasedByHours);
            this.hours = hoursPased;

            let hourRemainderByMinutes = (timePasedByHours - hoursPased) * 60;
            let minutesPased = Math.floor(hourRemainderByMinutes);
            this.minutes = minutesPased;

            let minuteRemainderBySeconds =
                (hourRemainderByMinutes - minutesPased) * 60;
            let secondsPased = Math.floor(minuteRemainderBySeconds);
            this.seconds = secondsPased;

            this.totalSeconds = Math.floor(timePased / 1000);

            // this.isNewWorktimeReady = true;

            this.setTimer();
        },
        startTimer() {
            if (this.$refs.form.validate()) {
                this.setTimer();
            }
        },
        setTimer(isChangingRunningTask = false) {
            this.timerOn = true;
            this.isNewWorktimeReady = false;

            if (!this.ongoingWorktime) {
                this.createWorktime(isChangingRunningTask);
            }

            this.timeInterval = setInterval(() => {
                this.seconds++;
                this.totalSeconds++;
                if (this.seconds == 60) {
                    this.minutes++;
                    this.seconds = 0;
                }
                if (this.minutes == 60) {
                    this.hours++;
                    this.minutes = 0;
                }

                let secondString =
                    this.seconds < 10
                        ? "0" + String(this.seconds)
                        : String(this.seconds);
                let minuteString =
                    this.minutes < 10
                        ? "0" + String(this.minutes)
                        : String(this.minutes);
                let hourString =
                    this.hours < 10
                        ? "0" + String(this.hours)
                        : String(this.hours);

                this.currentTime =
                    hourString + ":" + minuteString + ":" + secondString;
            }, 1000);
        },
        stopTimer(isDeleting = false, isChangingRunningTask = false) {
            if (!isDeleting) {
                this.updateWorktime(isChangingRunningTask);
            }

            clearInterval(this.timeInterval);
            this.seconds = 0;
            this.minutes = 0;
            this.hours = 0;
            this.totalSeconds = 0;
            this.currentTime = "00:00:00";

            this.createdTask = JSON.parse(JSON.stringify({}));
            this.createdWorktime = JSON.parse(JSON.stringify({}));

            this.timerOn = false;
            this.ongoingWorktime = null;
        },
        formatCurrentDate() {
            let date = new Date();
            let year = String(date.getUTCFullYear());
            let month =
                parseInt(date.getUTCMonth()) + 1 < 10
                    ? "0" + String(parseInt(date.getUTCMonth()) + 1)
                    : String(parseInt(date.getUTCMonth()) + 1);
            let day =
                date.getUTCDate() < 10
                    ? "0" + String(date.getUTCDate())
                    : String(date.getUTCDate());
            let hour =
                date.getUTCHours() < 10
                    ? "0" + String(date.getUTCHours())
                    : String(date.getUTCHours());
            let minute =
                date.getUTCMinutes() < 10
                    ? "0" + String(date.getUTCMinutes())
                    : String(date.getUTCMinutes());
            let second =
                date.getUTCSeconds() < 10
                    ? "0" + String(date.getUTCSeconds())
                    : String(date.getUTCSeconds());
            let formattedDate =
                year +
                "-" +
                month +
                "-" +
                day +
                " " +
                hour +
                ":" +
                minute +
                ":" +
                second;

            this.formattedDate = formattedDate;
        },
        createWorktime(isChangingRunningTask) {
            this.formatCurrentDate();
            this.newTask.started_at = this.formattedDate;

            axios
                .put(`/api/createWorktime`, this.newTask, {
                    headers: {
                        Authorization:
                            "Bearer " +
                            this.$store.state.tokenData.user.access_token,
                    },
                })
                .then((response) => {
                    this.newTask.task_id = response.data.newTaskId;
                    this.createdWorktime = response.data.worktime;
                    let index = this.tasks.findIndex(
                        (task) => task.id == response.data.newTaskId
                    );
                    if (index > -1) {
                        this.tasks[index].isRunning = true;
                        Vue.set(this.tasks, index, this.tasks[index]);
                        this.runningTaskPosition = index + 1;
                    }
                    if (!isChangingRunningTask) {
                        this.$refs.SnackMessage.showMessage(
                            "worktimes.worktime_saved",
                            "success"
                        );
                    }
                })
                .catch((error) => {
                    console.log(error);
                    this.$refs.SnackMessage.showMessage(
                        "general.error",
                        "error"
                    );
                });
        },
        updateWorktime(isChangingRunningTask) {
            this.formatCurrentDate();
            this.createdWorktime.finished_at = this.formattedDate;

            axios
                .put(`/api/updateWorktime`, this.createdWorktime, {
                    headers: {
                        Authorization:
                            "Bearer " +
                            this.$store.state.tokenData.user.access_token,
                    },
                })
                .then((response) => {
                    let index = this.tasks.findIndex(
                        (task) => task.id == response.data.id
                    );
                    if (index > -1) {
                        this.tasks.splice(index, 1);
                    }

                    this.runningTaskPosition = null;
                    this.tasks.unshift(response.data);
                    this.createdTask = response.data;

                    if (this.withoutTasks) {
                        this.getWorktimes();
                    }

                    if (!isChangingRunningTask) {
                        this.snackMessage(
                            "worktimes.worktime_saved",
                            "success"
                        );
                    }
                })
                .catch((error) => {
                    console.log(error);
                    this.snackMessage("general.error", "error");
                });
        },
        startTimerExistingTask(task, taskIndex) {
            if (!this.timerOn) {
                this.newTask.name = task.name;
                this.newTask.project_id = task.project_id;
                this.runningTaskPosition = taskIndex + 1;
                this.setTimer();
            } else {
                var message =
                    this.$t("worktimes.task_has_finished_cz") +
                    " " +
                    '"' +
                    this.newTask.name +
                    '"' +
                    " " +
                    this.$t("worktimes.task_has_finished") +
                    ", " +
                    this.$t("worktimes.task_starts") +
                    " " +
                    '"' +
                    task.name +
                    '".';
                this.snackMessage(message, "info", true);

                this.stopTimer(false, true);
                this.newTask.name = task.name;
                this.newTask.project_id = task.project_id;
                this.runningTaskPosition = taskIndex + 1;
                this.setTimer(true);
            }
        },
        prepareWorkTime() {
            this.newTask = JSON.parse(JSON.stringify(this.defaultNewTask));
            this.isNewWorktimeReady = true;
        },

        confirmDeleteTask(task, taskIndex) {
            //parameters null when new task is running
            this.dialog.confirmationTask = true;
            if (task != null) {
                this.taskToDelete = JSON.parse(JSON.stringify(task));
                this.taskIndexToDelete = taskIndex;
                this.dialog.confirmationTaskName = task.name;
            } else {
                this.dialog.confirmationTaskName = this.newTask.name;
            }
        },

        cancelDelete() {
            this.dialog.confirmationTask = false;
            this.confirmationTaskName = "";
            this.taskToDelete = JSON.parse(JSON.stringify(null));
            this.taskIndexToDelete = JSON.parse(JSON.stringify(null));
        },

        deleteTask() {
            this.dialog.confirmationTask = false;

            if (this.taskToDelete == null) {
                var taskId = this.newTask.task_id;
                this.stopTimer(true, false);
            } else {
                var taskId = this.taskToDelete.id;
                if (this.timerOn && this.taskToDelete.isRunning) {
                    this.stopTimer(true, false);
                }
            }
            axios
                .delete(`/api/deleteTask/${taskId}`, {
                    headers: {
                        Authorization:
                            "Bearer " +
                            this.$store.state.tokenData.user.access_token,
                    },
                })
                .then((response) => {
                    if (!!this.runningTaskPosition) {
                        if (this.taskIndexToDelete == null) {
                            this.tasks.splice(this.runningTaskPosition - 1, 1);
                        } else {
                            this.tasks.splice(this.taskIndexToDelete, 1);
                        }

                        if (this.taskToDelete && this.taskToDelete.isRunning) {
                            this.newTask = JSON.parse(
                                JSON.stringify(this.defaultNewTask)
                            );
                        }

                        this.runningTaskPosition = null;
                    } else {
                        this.tasks.splice(this.taskIndexToDelete, 1);
                    }

                    this.taskToDelete = JSON.parse(JSON.stringify(null));
                    this.taskIndexToDelete = JSON.parse(JSON.stringify(null));

                    this.$refs.SnackMessage.showMessage(
                        "worktimes.task_deleted_successfully",
                        "success"
                    );
                })
                .catch((error) => {
                    console.log(error);
                    this.$refs.SnackMessage.showMessage(
                        "general.error",
                        "error"
                    );
                });
        },
        refreshTimer() {
            if (this.isPageLoaded && this.timerOn && !document.hidden) {
                clearInterval(this.timeInterval);
                this.ongoingWorktime = null;
                axios
                    .get(
                        `/api/getOngoingWorktime/${this.$store.state.profile.id}`,
                        {
                            headers: {
                                Authorization:
                                    "Bearer " +
                                    this.$store.state.tokenData.user
                                        .access_token,
                            },
                        }
                    )
                    .then((response) => {
                        this.ongoingWorktime = response.data
                            ? response.data
                            : null;

                        if (!!this.ongoingWorktime) {
                            this.updateOngoingTime();
                        }
                    })
                    .catch((error) => {
                        console.log(error);
                        this.$refs.SnackMessage.showMessage(
                            "general.error",
                            "error"
                        );
                    });
            }
        },
        getWorktimesByPage(pageOptions) {
            if (this.requestWorktimesCounter > 0) {
                this.page = pageOptions.page;
                this.itemsPerPage = pageOptions.itemsPerPage;
                this.getWorktimes(true);

                this.taskNameSearchWorktime = JSON.parse(JSON.stringify({}));
            }
            ++this.requestWorktimesCounter;
        },
        searchTasks() {
            if (
                (this.search.length > 2 || this.search.length < 1) &&
                !this.isSearching
            ) {
                this.isSearching = true;
                this.page = 1;
                this.getWorktimes(true);
            }
        },
        searchTaskByDelete() {
            //search after holding continuously delete or backspace keys and a search query is ongoing
            if (this.isSearching) {
                setTimeout(() => {
                    this.searchTasks();
                }, 300);
            }
        },
        async searchForNewTask() {
            if (
                !!this.taskNameSearch &&
                this.taskNameSearch.length > 2 &&
                !this.isSearchingForNewTask
            ) {
                this.isSearchingForNewTask = true;
                await axios
                    .get(
                        `/api/searchForNewTask/${this.$store.state.profile.id}/${this.taskNameSearch}`,
                        {
                            headers: {
                                Authorization:
                                    "Bearer " +
                                    this.$store.state.tokenData.user
                                        .access_token,
                            },
                        }
                    )
                    .then((response) => {
                        this.similarTasks = response.data.tasks;

                        setTimeout(() => {
                            this.isSearchingForNewTask = false;
                        }, 300);
                    })
                    .catch((error) => {
                        console.log(error);
                        this.$refs.SnackMessage.showMessage(
                            "general.error",
                            "error"
                        );
                    });
            }

            if (!this.taskNameSearch || this.taskNameSearch.length <= 2) {
                this.similarTasks = [];
            }
        },
        async searchForNewTaskWorktime(taskIndex, worktimeIndex) {
            var searchName =
                this.taskNameSearchWorktime[taskIndex + "-" + worktimeIndex];
            this.tasks[taskIndex].worktimes[worktimeIndex].task_name =
                this.taskNameSearchWorktime[taskIndex + "-" + worktimeIndex];
            if (
                !!searchName &&
                searchName.length > 2 &&
                !this.isSearchingForNewTaskWorktime
            ) {
                this.isSearchingForNewTaskWorktime = true;
                await axios
                    .get(
                        `/api/searchForNewTask/${this.$store.state.profile.id}/${searchName}`,
                        {
                            headers: {
                                Authorization:
                                    "Bearer " +
                                    this.$store.state.tokenData.user
                                        .access_token,
                            },
                        }
                    )
                    .then((response) => {
                        this.similarTasksWorktime = response.data.tasks;

                        setTimeout(() => {
                            this.isSearchingForNewTaskWorktime = false;
                        }, 300);
                    })
                    .catch((error) => {
                        console.log(error);
                        this.$refs.SnackMessage.showMessage(
                            "general.error",
                            "error"
                        );
                    });
            }

            if (!searchName || searchName.length <= 2) {
                this.similarTasksWorktime = [];
            }
        },
        async setProjectSuggestion() {
            await axios
                .get(
                    `/api/setProjectSuggestion/${this.$store.state.profile.id}/${this.newTask.name}`,
                    {
                        headers: {
                            Authorization:
                                "Bearer " +
                                this.$store.state.tokenData.user.access_token,
                        },
                    }
                )
                .then((response) => {
                    if (response.data.taskHasOneProject) {
                        this.newTask.project_id =
                            response.data.suggestedProjectId;
                    }
                })
                .catch((error) => {
                    console.log(error);
                    this.$refs.SnackMessage.showMessage(
                        "general.error",
                        "error"
                    );
                });
        },

        searchForNewTaskByDelete(searchFunction, taskIndex, worktimeIndex) {
            //search after holding continuously delete or backspace keys and a search query is ongoing
            if ((searchFunction = "task" && this.isSearchingForNewTask)) {
                console.log("gonna search task");
                setTimeout(() => {
                    this.searchForNewTask();
                }, 400);
            }
            if (
                (searchFunction =
                    "worktime" && this.isSearchingForNewTaskWorktime)
            ) {
                console.log("gonna search task wokrtime");
                setTimeout(() => {
                    this.searchForNewTaskWorktime(taskIndex, worktimeIndex);
                }, 400);
            }
        },

        editWorktime(taskIndex, worktimeIndex) {
            this.tasks[taskIndex].worktimes[worktimeIndex].is_editing = true;

            this.tasks[taskIndex].worktimes[worktimeIndex].task_name_temporal =
                this.tasks[taskIndex].worktimes[worktimeIndex].task_name;

            this.tasks[taskIndex].worktimes[worktimeIndex].project_id_temporal =
                this.tasks[taskIndex].worktimes[worktimeIndex].project_id;

            this.tasks[taskIndex].worktimes[worktimeIndex].duration_temporal =
                this.tasks[taskIndex].worktimes[
                    worktimeIndex
                ].formattedDuration;

            this.tasks[taskIndex].worktimes[
                worktimeIndex
            ].started_short_formatted_temporal =
                this.tasks[taskIndex].worktimes[
                    worktimeIndex
                ].started_at_short_formatted;

            this.tasks[taskIndex].worktimes[
                worktimeIndex
            ].started_short_standard_temporal =
                this.tasks[taskIndex].worktimes[
                    worktimeIndex
                ].started_at_short_standard;

            this.tasks[taskIndex].worktimes[
                worktimeIndex
            ].started_at_time_temporal =
                this.tasks[taskIndex].worktimes[
                    worktimeIndex
                ].started_at_time_formatted;

            Vue.set(this.tasks, taskIndex, this.tasks[taskIndex]);
        },

        confirmDeleteWorktime(taskIndex, worktimeIndex) {
            this.dialog.confirmationWorktime = true;
            this.dialog.confirmationWorktimeName = this.tasks[taskIndex].name;
            this.worktimeToDelete = JSON.parse(
                JSON.stringify(this.tasks[taskIndex].worktimes[worktimeIndex])
            );
            this.worktimeIndexesToDelete.task = taskIndex;
            this.worktimeIndexesToDelete.worktime = worktimeIndex;
        },
        cancelDeleteWorktime() {
            this.dialog.confirmationWorktime = false;
            this.dialog.confirmationWorktimeName = "";
            this.worktimeToDelete = JSON.parse(JSON.stringify(null));
            this.worktimeIndexesToDelete.task = null;
            this.worktimeIndexesToDelete.worktime = null;
        },
        deleteWorktime() {
            this.dialog.confirmationWorktime = false;

            var worktimeId = this.worktimeToDelete.id;
            axios
                .delete(`/api/deleteWorktime/${worktimeId}`, {
                    headers: {
                        Authorization:
                            "Bearer " +
                            this.$store.state.tokenData.user.access_token,
                    },
                })
                .then((response) => {
                    if (response.data.isTaskDeleted) {
                        this.tasks.splice(this.worktimeIndexesToDelete.task, 1);
                    } else {
                        this.tasks[
                            this.worktimeIndexesToDelete.task
                        ].worktimes.splice(
                            this.worktimeIndexesToDelete.worktime,
                            1
                        );

                        this.tasks[
                            this.worktimeIndexesToDelete.task
                        ].timeInformation.formattedDuration =
                            response.data.task.timeInformation.formattedDuration;
                        this.tasks[
                            this.worktimeIndexesToDelete.task
                        ].worktimes_count = response.data.task.worktimes_count;
                        this.tasks[
                            this.worktimeIndexesToDelete.task
                        ].formattedDate = response.data.task.formattedDate;

                        Vue.set(
                            this.tasks,
                            this.worktimeIndexesToDelete.task,
                            this.tasks[this.worktimeIndexesToDelete.task]
                        );

                        this.$refs.SnackMessage.showMessage(
                            "worktimes.worktime_deleted_successfully",
                            "success"
                        );
                    }
                })
                .catch((error) => {
                    console.log(error);
                    this.$refs.SnackMessage.showMessage(
                        "general.error",
                        "error"
                    );
                });
        },
        cancelWorktimeEdition(taskIndex, worktimeIndex) {
            this.tasks[taskIndex].worktimes[worktimeIndex].is_editing = false;

            this.tasks[taskIndex].worktimes[worktimeIndex].task_name =
                this.tasks[taskIndex].worktimes[
                    worktimeIndex
                ].task_name_temporal;
            this.tasks[taskIndex].worktimes[worktimeIndex].task_name_temporal =
                "";

            this.tasks[taskIndex].worktimes[worktimeIndex].project_id =
                this.tasks[taskIndex].worktimes[
                    worktimeIndex
                ].project_id_temporal;
            this.tasks[taskIndex].worktimes[
                worktimeIndex
            ].project_id_temporal = 0;

            this.tasks[taskIndex].worktimes[worktimeIndex].formattedDuration =
                this.tasks[taskIndex].worktimes[
                    worktimeIndex
                ].duration_temporal;
            this.tasks[taskIndex].worktimes[worktimeIndex].duration_temporal =
                null;

            this.tasks[taskIndex].worktimes[
                worktimeIndex
            ].started_at_short_formatted =
                this.tasks[taskIndex].worktimes[
                    worktimeIndex
                ].started_short_formatted_temporal;

            this.tasks[taskIndex].worktimes[
                worktimeIndex
            ].started_at_short_standard =
                this.tasks[taskIndex].worktimes[
                    worktimeIndex
                ].started_short_standard_temporal;

            this.tasks[taskIndex].worktimes[
                worktimeIndex
            ].started_at_time_formatted =
                this.tasks[taskIndex].worktimes[
                    worktimeIndex
                ].started_at_time_temporal;
            this.tasks[taskIndex].worktimes[
                worktimeIndex
            ].started_at_time_temporal = null;

            Vue.set(this.tasks, taskIndex, this.tasks[taskIndex]);
        },

        async saveWorktime(taskIndex, worktimeIndex) {
            if (
                this.$refs[
                    "formWorktime" + "-" + taskIndex + "-" + worktimeIndex
                ][0].validate()
            ) {
                await axios
                    .post(
                        "/api/saveWorktime",
                        this.tasks[taskIndex].worktimes[worktimeIndex],
                        {
                            headers: {
                                Authorization:
                                    "Bearer " +
                                    this.$store.state.tokenData.user
                                        .access_token,
                            },
                        }
                    )
                    .then((response) => {
                        if (response.data.isSameTask) {
                            this.tasks[taskIndex].worktimes.splice(
                                worktimeIndex,
                                1,
                                response.data.worktime
                            );

                            this.tasks[
                                taskIndex
                            ].timeInformation.formattedDuration =
                                response.data.task.timeInformation.formattedDuration;
                            this.tasks[taskIndex].worktimes_count =
                                response.data.task.worktimes_count;
                            this.tasks[taskIndex].formattedDate =
                                response.data.task.formattedDate;

                            Vue.set(
                                this.tasks,
                                taskIndex,
                                this.tasks[taskIndex]
                            );
                        } else {
                            if (response.data.isPreviousTaskDeleted) {
                                this.tasks.splice(taskIndex, 1);
                            } else {
                                this.tasks[taskIndex].worktimes.splice(
                                    worktimeIndex,
                                    1
                                );

                                this.tasks[
                                    taskIndex
                                ].timeInformation.formattedDuration =
                                    response.data.previousTask.timeInformation.formattedDuration;
                                this.tasks[taskIndex].worktimes_count =
                                    response.data.previousTask.worktimes_count;
                                this.tasks[taskIndex].formattedDate =
                                    response.data.previousTask.formattedDate;

                                Vue.set(
                                    this.tasks,
                                    taskIndex,
                                    this.tasks[taskIndex]
                                );
                            }

                            var index = this.tasks.findIndex(
                                (task) => task.id == response.data.taskId
                            );
                            if (index > -1) {
                                this.tasks.splice(index, 1);
                            }
                            this.tasks.unshift(response.data.task);

                            if (response.data.isNewTask) {
                                this.isTaskCreated = true;
                                setTimeout(() => {
                                    this.isTaskCreated = false;
                                }, 2000);
                            }
                        }
                        this.snackMessage(
                            "worktimes.worktime_saved",
                            "success"
                        );
                    })
                    .catch((error) => {
                        console.log(error);
                        this.$refs.SnackMessage.showMessage(
                            "general.error",
                            "error"
                        );
                    });
            }
        },
        prepareWorkTimeDate(taskIndex, worktimeIndex) {
            this.tasks[taskIndex].worktimes[
                worktimeIndex
            ].is_started_date_picker_visible = false;

            var new_short_formatted = new Date(
                this.tasks[taskIndex].worktimes[worktimeIndex]
                    .started_at_short_standard + "T12:00:00Z"
            )
                .toLocaleString(this.datePickerLocale, {
                    day: "2-digit",
                    month: "2-digit",
                    year: "numeric",
                    timeZone: "UTC",
                })
                .replace(/\s/g, "")
                .replace(/\//g, "-");

            this.tasks[taskIndex].worktimes[
                worktimeIndex
            ].started_at_short_formatted = new_short_formatted;

            Vue.set(this.tasks, taskIndex, this.tasks[taskIndex]);
        },

        onResize() {
            if (window.innerWidth < 850) {
                this.isMobile = true;
            } else {
                this.isMobile = false;
            }
        },
        setEditionColumnWidth(isEditing) {
            var columns;
            if (!this.isMobile && isEditing) {
                columns = "2";
            }
            if (!this.isMobile && !isEditing) {
                columns = "";
            }
            if (this.isMobile) {
                columns = "12";
            }
            return columns;
        },
    },
};
</script>
