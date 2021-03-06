/**
 * @module core/worker/admin-worker
 */

import LoginService from 'src/core/service/login.service';
import ScheduledTaskService from 'src/core/service/api/scheduled-task.api.service';
import MessageQueueService from 'src/core/service/api/message-queue.api.service';
import Axios from 'axios';

onmessage = ({ data: { context, bearerAuth, host, pollingConfig } }) => {
    const baseURL = process.env.NODE_ENV !== 'production' ? `${host}${context.apiResourcePath}` : context.apiResourcePath;
    const client = Axios.create({
        baseURL: baseURL
    });

    const loginService = LoginService(client, context, bearerAuth);
    const scheduledTaskService = new ScheduledTaskService(client, loginService);
    const messageQueueService = new MessageQueueService(client, loginService);

    scheduledTaskService.getMinRunInterval().then((response) => {
        if (response.minRunInterval > 0) {
            const timeout = response.minRunInterval * 1000;
            runTasks(scheduledTaskService, timeout);
        }
    });

    Object.keys(pollingConfig).forEach((receiver) => {
        if (pollingConfig[receiver] > 0) {
            consumeMessages(messageQueueService, receiver, pollingConfig[receiver] * 1000);
        }
    });
};

function runTasks(scheduledTaskService, timeout) {
    scheduledTaskService.runTasks().catch((error) => {
        const { response: { status } } = error;

        if (status === 401) {
            postMessage('expiredToken');
        }
    });

    setTimeout(() => {
        runTasks(scheduledTaskService, timeout);
    }, timeout);
}

function consumeMessages(messageQueueService, receiver, timeout) {
    messageQueueService.consume(receiver)
        .then((response) => {
            if (response.handledMessages > 0) {
                consumeMessages(messageQueueService, receiver, timeout);
            } else {
                setTimeout(() => {
                    consumeMessages(messageQueueService, receiver, timeout);
                }, timeout);
            }
        })
        .catch((error) => {
            const { response: { status } } = error;

            if (status === 401) {
                postMessage('expiredToken');
            }
        });
}
